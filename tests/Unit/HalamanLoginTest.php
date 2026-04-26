<?php

namespace Tests\Unit;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White Box Testing — Fitur: Halaman Login
 * Controller : AuthenticatedSessionController@store
 * Request    : LoginRequest (rules + authenticate logic)
 * Logika yang diuji:
 *   - Validasi field required (nip, password)
 *   - Validasi digits:18 untuk NIP
 *   - Validasi max:12 untuk password
 *   - if (!$user) → throw NIP tidak ditemukan
 *   - if (!Auth::attempt) → throw Password salah
 *   - if ($user->role === 'admin') → redirect admin.dashboard
 *   - else → redirect user.dashboard
 *   - RateLimiter: tooManyAttempts setelah 3 kali gagal
 */
class HalamanLoginTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper: buat user dengan NIP spesifik ───────────────────────
    private function buatUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'nip'      => '197810162003122008',
            'password' => Hash::make('123456'),
            'role'     => 'user',
        ], $overrides));
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Reset rate limiter sebelum setiap test agar tidak saling mengganggu
        RateLimiter::clear('197810162003122008|127.0.0.1');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Validasi: login gagal jika NIP tidak diisi (required)
    // Memetakan: rules → 'nip' => ['required', ...]
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_nip_tidak_diisi()
    {
        $response = $this->post(route('login'), [
            'password' => '123456',
        ]);

        // Harus ada error validasi untuk field nip
        $response->assertSessionHasErrors('nip');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Validasi: login gagal jika password tidak diisi (required)
    // Memetakan: rules → 'password' => ['required', ...]
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_password_tidak_diisi()
    {
        $response = $this->post(route('login'), [
            'nip' => '197810162003122008',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Validasi: login gagal jika NIP bukan 18 digit (digits:18)
    // Memetakan: rules → 'nip' => ['digits:18']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_nip_kurang_dari_18_digit()
    {
        $response = $this->post(route('login'), [
            'nip'      => '12345',
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('nip');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Validasi: login gagal jika password melebihi 12 karakter (max:12)
    // Memetakan: rules → 'password' => ['max:12']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_password_lebih_dari_12_karakter()
    {
        $response = $this->post(route('login'), [
            'nip'      => '197810162003122008',
            'password' => 'passwordpanjangbanget123', // >12 karakter
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Logika if: login gagal jika NIP tidak ditemukan di database
    // Memetakan: if (!$user) → throw ValidationException 'NIP tidak ditemukan.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_nip_tidak_ditemukan_di_database()
    {
        // Tidak ada user di database dengan NIP ini
        $response = $this->post(route('login'), [
            'nip'      => '000000000000000000', // 18 digit tapi tidak ada
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('nip');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Logika if: login gagal jika NIP benar tapi password salah
    // Memetakan: if (!Auth::attempt) → throw 'Password yang Anda masukkan salah.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_jika_nip_benar_tapi_password_salah()
    {
        $this->buatUser();

        $response = $this->post(route('login'), [
            'nip'      => '197810162003122008',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Logika if: login berhasil dengan NIP dan password yang benar
    // Memetakan: Auth::attempt() berhasil → user terauthentikasi
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_berhasil_dengan_nip_dan_password_yang_benar()
    {
        $user = $this->buatUser(['role' => 'user']);

        $response = $this->post(route('login'), [
            'nip'      => '197810162003122008',
            'password' => '123456',
        ]);

        // User harus terauthentikasi
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Logika if ($user->role === 'admin'): diarahkan ke admin.dashboard
    // Memetakan: if ($user->role === 'admin') → redirect()->route('admin.dashboard')
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_admin_diarahkan_ke_dashboard_admin()
    {
        $this->buatUser(['role' => 'admin']);

        $response = $this->post(route('login'), [
            'nip'      => '197810162003122008',
            'password' => '123456',
        ]);

        // Admin harus diarahkan ke route admin.dashboard
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 9 — Logika else: user biasa diarahkan ke user.dashboard
    // Memetakan: else → redirect()->route('user.dashboard')
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_user_biasa_diarahkan_ke_dashboard_user()
    {
        $bidang = Bidang::create(['nama' => 'Bidang Test']);
        $this->buatUser(['role' => 'user', 'bidang_id' => $bidang->id]);

        $response = $this->post(route('login'), [
            'nip'      => '197810162003122008',
            'password' => '123456',
        ]);

        // User biasa diarahkan ke dashboard user
        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 10 — Logika RateLimiter: akses dibatasi setelah banyak percobaan gagal
    // Memetakan: ensureIsNotRateLimited() → tooManyAttempts(key, 3)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function login_gagal_setelah_terlalu_banyak_percobaan()
    {
        $this->buatUser();

        $nip = '197810162003122008';

        // Tembak Rate Limiter 5 kali
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'nip'      => $nip,

                // PENTING: Password salah ini HARUS 12 karakter atau kurang!
                // Jika lebih dari 12, dia ditolak validasi awal dan tidak dihitung gagal login.
                'password' => 'salah123',
            ]);
        }

        // Percobaan terakhir yang seharusnya diblokir oleh Rate Limiter
        $response = $this->post(route('login'), [
            'nip'      => $nip,
            'password' => '123456',
        ]);

        // Assert session error
        $response->assertSessionHasErrors('nip');
        $this->assertGuest();
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 11 — Logout: sesi berakhir setelah logout
    // Memetakan: Auth::guard('web')->logout() → assertGuest()
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function logout_berhasil_mengakhiri_sesi_user()
    {
        $user = $this->buatUser();
        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        // Setelah logout, user tidak terauthentikasi
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 12 — Halaman login mengembalikan view yang benar
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function halaman_login_mengembalikan_view_auth_login()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }
}
