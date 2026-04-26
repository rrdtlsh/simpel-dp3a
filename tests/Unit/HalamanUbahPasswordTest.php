<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White Box Testing — Fitur: Halaman Ubah Password
 * Controller : ProfileController@changePassword
 * Logika yang diuji:
 *   - Validasi required: current_password, new_password
 *   - Validasi confirmed: new_password_confirmation harus cocok
 *   - Validasi min:6 dan max:18 untuk password baru
 *   - if (!Hash::check(...)) → 422 'Password saat ini yang Anda masukkan salah.'
 *   - if ($current === $new) → 422 'Tidak ada perubahan, masukkan password baru.'
 *   - else → simpan Hash::make($new_password) dan return 200
 */
class HalamanUbahPasswordTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper: buat user dan login ─────────────────────────────────
    private function loginSebagaiUser(string $password = '123456'): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nip'      => '197810162003122008',
            'password' => Hash::make($password),
            'role'     => 'user',
        ]);

        $this->actingAs($user);

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Validasi: gagal jika current_password tidak diisi (required)
    // Memetakan: 'current_password' => ['required']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_current_password_tidak_diisi()
    {
        $this->loginSebagaiUser();

        $response = $this->postJson(route('profile.change-password'), [
            'new_password'              => '1234567',
            'new_password_confirmation' => '1234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Validasi: gagal jika new_password tidak diisi (required)
    // Memetakan: 'new_password' => ['required']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_new_password_tidak_diisi()
    {
        $this->loginSebagaiUser();

        $response = $this->postJson(route('profile.change-password'), [
            'current_password' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Validasi: gagal jika new_password dan konfirmasi tidak cocok (confirmed)
    // Memetakan: 'new_password' => ['confirmed'] → 'Konfirmasi password baru tidak cocok.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_konfirmasi_password_tidak_cocok()
    {
        $this->loginSebagaiUser();

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '1234567',
            'new_password_confirmation' => '7654321', // tidak cocok
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.new_password.0', 'Konfirmasi password baru tidak cocok.');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Validasi: gagal jika password baru kurang dari 6 karakter (min:6)
    // Memetakan: 'new_password' => ['min:6'] → 'Password baru minimal 6 karakter.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_new_password_kurang_dari_6_karakter()
    {
        $this->loginSebagaiUser();

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '123',  // < 6 karakter
            'new_password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Validasi: gagal jika password baru lebih dari 18 karakter (max:18)
    // Memetakan: 'new_password' => ['max:18'] → 'Password baru maksimal 18 karakter.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_new_password_lebih_dari_18_karakter()
    {
        $this->loginSebagaiUser();

        $terlalu_panjang = 'abc@@@@@@@@@@@@@@@@@@@@@@'; // > 18 karakter

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => $terlalu_panjang,
            'new_password_confirmation' => $terlalu_panjang,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Logika if: gagal jika current_password salah (Hash::check gagal)
    // Memetakan: if (!Hash::check($request->current_password, $user->password))
    //            → return 422 'Password saat ini yang Anda masukkan salah.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_password_saat_ini_salah()
    {
        $this->loginSebagaiUser('123456');

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123455', // password saat ini yang salah
            'new_password'              => '1234567',
            'new_password_confirmation' => '1234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', 'Password saat ini yang Anda masukkan salah.');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Logika if: gagal jika password baru sama dengan password lama
    // Memetakan: if ($request->current_password === $request->new_password)
    //            → return 422 'Tidak ada perubahan, masukkan password baru.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_gagal_jika_password_baru_sama_dengan_password_lama()
    {
        $this->loginSebagaiUser('123456');

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '123456', // sama dengan password sekarang
            'new_password_confirmation' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.new_password.0', 'Tidak ada perubahan, masukkan password baru.');
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Logika else: berhasil ubah password dengan semua kondisi terpenuhi
    // Memetakan: $user->password = Hash::make($request->new_password); → return 200
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_berhasil_dengan_semua_kondisi_valid()
    {
        $user = $this->loginSebagaiUser('123456');

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '1234567', // password baru berbeda
            'new_password_confirmation' => '1234567',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password berhasil diubah.']);

        // Verifikasi: password baru benar-benar tersimpan di database
        $user->refresh();
        $this->assertTrue(Hash::check('1234567', $user->password));
        $this->assertFalse(Hash::check('123456', $user->password)); // password lama sudah tidak berlaku
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 9 — Halaman ubah password hanya bisa diakses oleh user yang login
    // Memetakan: middleware 'auth' → redirect ke login jika belum login
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function halaman_ubah_password_tidak_bisa_diakses_tanpa_login()
    {
        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '1234567',
            'new_password_confirmation' => '1234567',
        ]);

        // Harus ditolak (401 Unauthorized untuk JSON request)
        $response->assertStatus(401);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 10 — Password baru boleh mengandung huruf, angka, dan simbol
    // Memetakan: 'new_password' => ['string'] → tidak ada batasan karakter
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ubah_password_berhasil_dengan_password_mengandung_huruf_angka_simbol()
    {
        $user = $this->loginSebagaiUser('123456');

        $response = $this->postJson(route('profile.change-password'), [
            'current_password'          => '123456',
            'new_password'              => '123abc@!', // mengandung huruf, angka, simbol
            'new_password_confirmation' => '123abc@!',
        ]);

        $response->assertStatus(200);

        // Verifikasi password dengan simbol tersimpan dengan benar
        $user->refresh();
        $this->assertTrue(Hash::check('123abc@!', $user->password));
    }
}
