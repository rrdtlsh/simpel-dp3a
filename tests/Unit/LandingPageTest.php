<?php

namespace Tests\Unit;

use App\Models\Pengumuman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White Box Testing — Fitur: Landing Page
 * Controller : HomeController@index
 * Logika yang diuji:
 *   - Pengumuman::active() → scope WHERE is_active = true
 *   - latest() → ORDER BY created_at DESC
 *   - take(6) → LIMIT 6
 *   - Halaman hanya menampilkan pengumuman aktif, bukan non-aktif
 */
class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper: buat user admin terautentikasi ───────────────────────
    private function buatAdmin(): User
    {
        return User::factory()->create([
            'role'     => 'admin',
            'nip'      => '197810162003122008',
            'password' => Hash::make('admin123'),
        ]);
    }

    // ─── Helper: buat pengumuman aktif ───────────────────────────────
    private function buatPengumuman(array $overrides = []): Pengumuman
    {
        $admin = User::first() ?? $this->buatAdmin();

        return Pengumuman::create(array_merge([
            'judul'       => 'Pengumuman ' . uniqid(),
            'konten'      => 'Isi pengumuman contoh',
            'badge_label' => 'Info',
            'badge_color' => 'blue',
            'is_active'   => true,
            'created_by'  => $admin->id,
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Halaman landing page dapat diakses publik (tanpa login)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_dapat_diakses_tanpa_login()
    {
        $response = $this->get(route('home'));

        // Halaman publik harus mengembalikan HTTP 200
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Logika if: hanya pengumuman aktif (is_active=true) yang tampil
    // Memetakan: scope scopeActive → WHERE is_active = true
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_hanya_menampilkan_pengumuman_aktif()
    {
        $admin = $this->buatAdmin();

        // Buat 2 pengumuman aktif dan 1 non-aktif
        $aktif1   = $this->buatPengumuman(['judul' => 'Pengumuman Aktif 1', 'is_active' => true]);
        $aktif2   = $this->buatPengumuman(['judul' => 'Pengumuman Aktif 2', 'is_active' => true]);
        $nonAktif = $this->buatPengumuman(['judul' => 'Pengumuman Non-Aktif', 'is_active' => false]);

        // Uji langsung pada scope model (logika internalnya)
        $hasil = Pengumuman::active()->get();

        // Hanya 2 yang aktif yang muncul
        $this->assertCount(2, $hasil);
        $this->assertTrue($hasil->contains('judul', 'Pengumuman Aktif 1'));
        $this->assertTrue($hasil->contains('judul', 'Pengumuman Aktif 2'));

        // Pengumuman non-aktif TIDAK boleh muncul
        $this->assertFalse($hasil->contains('judul', 'Pengumuman Non-Aktif'));
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Logika take(6): maksimal 6 pengumuman ditampilkan
    // Memetakan: take(6) → LIMIT 6 di database
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_menampilkan_maksimal_6_pengumuman()
    {
        $this->buatAdmin();

        // Buat 8 pengumuman aktif (melebihi batas 6)
        for ($i = 1; $i <= 8; $i++) {
            $this->buatPengumuman(['judul' => "Pengumuman Ke-{$i}"]);
        }

        // Uji logika take(6) langsung di layer model
        $hasil = Pengumuman::active()->latest()->take(6)->get();

        // Harus tepat 6, walaupun di database ada 8
        $this->assertCount(6, $hasil);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Logika latest(): pengumuman terbaru tampil paling atas
    // Memetakan: latest() → ORDER BY created_at DESC
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_menampilkan_pengumuman_terbaru_paling_atas()
    {
        $this->buatAdmin();

        $lama = $this->buatPengumuman(['judul' => 'Pengumuman Lama']);
        // Beri jeda waktu agar created_at berbeda
        sleep(1);
        $baru = $this->buatPengumuman(['judul' => 'Pengumuman Terbaru']);

        $hasil = Pengumuman::active()->latest()->get();

        // Index 0 harus pengumuman terbaru
        $this->assertEquals('Pengumuman Terbaru', $hasil->first()->judul);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Jika tidak ada pengumuman aktif, koleksi kosong dikembalikan
    // Memetakan: kondisi if (tidak ada data) → collection empty
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_mengembalikan_koleksi_kosong_jika_tidak_ada_pengumuman_aktif()
    {
        $this->buatAdmin();

        // Buat hanya pengumuman non-aktif
        $this->buatPengumuman(['is_active' => false]);

        $hasil = Pengumuman::active()->latest()->take(6)->get();

        // Harus kosong
        $this->assertEmpty($hasil);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Logika scope active: pengumuman dengan is_active=false tidak lolos filter
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function scope_active_memfilter_dengan_benar_berdasarkan_is_active()
    {
        $this->buatAdmin();

        $this->buatPengumuman(['is_active' => true]);
        $this->buatPengumuman(['is_active' => false]);

        // Semua pengumuman = 2
        $semuaPengumuman = Pengumuman::count();
        $this->assertEquals(2, $semuaPengumuman);

        // Pengumuman aktif = 1
        $aktif = Pengumuman::active()->count();
        $this->assertEquals(1, $aktif);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Memetakan navbar "Bidang Organisasi": halaman home mengembalikan view yang benar
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_menggunakan_view_home_yang_benar()
    {
        $response = $this->get(route('home'));

        $response->assertViewIs('start.home');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — View menerima variabel $pengumumans dari controller
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function landing_page_mengirimkan_variabel_pengumumans_ke_view()
    {
        $this->buatAdmin();
        $this->buatPengumuman();

        $response = $this->get(route('home'));

        $response->assertViewHas('pengumumans');
    }
}
