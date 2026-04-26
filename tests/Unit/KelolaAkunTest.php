<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White-Box Testing: Fitur Kelola Akun (Manage Users)
 * Sistem: SIMPEL DP3A
 * Menguji logika internal ManageUserController (CRUD akun oleh Admin)
 */
class KelolaAkunTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Bidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat data bidang sebagai referensi
        $this->bidang = Bidang::create(['nama' => 'Perlindungan Perempuan']);
        
        // 2. Buat Admin dan jadikan sebagai user yang login
        /** @var User $admin */
        $this->admin = User::factory()->create(['role' => 'admin', 'nip' => '111111111111111111']);
        $this->actingAs($this->admin);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Tambah Akun Berhasil
    // Memetakan: POST ke admin.manage_users.store
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_berhasil_dengan_data_valid()
    {
        $response = $this->postJson(route('admin.manage_users.store'), [ // Gunakan postJson
            'name'                  => 'Zahra Nabila',
            'nip'                   => '200505132027112030',
            'email'                 => 'zahra@email.com',
            'bidang_id'             => $this->bidang->id,
            'role'                  => 'user',
            'password'              => 'password123',
        ]);

        // Karena controller me-return JSON dengan status 200
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['nip' => '200505132027112030', 'name' => 'Zahra Nabila']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Gagal jika kosong
    // Memetakan: Validasi 'required' di StoreUserRequest
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_gagal_jika_field_wajib_kosong()
    {
        $response = $this->post(route('admin.manage_users.store'), [
            'name'  => '',
            'nip'   => '',
            'email' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'nip', 'email', 'password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Gagal NIP bukan 18 digit
    // Memetakan: Validasi 'size:18' di StoreUserRequest
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_gagal_jika_nip_bukan_18_digit()
    {
        $response = $this->post(route('admin.manage_users.store'), [
            'name'                  => 'Test User',
            'nip'                   => '123456', // Hanya 6 digit
            'email'                 => 'test@email.com',
            'bidang_id'             => $this->bidang->id,
            'role'                  => 'user',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['nip']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Gagal email tidak valid
    // Memetakan: Validasi 'email' di StoreUserRequest
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_gagal_jika_email_format_tidak_valid()
    {
        $response = $this->post(route('admin.manage_users.store'), [
            'name'                  => 'Test User',
            'nip'                   => '200505132027112031',
            'email'                 => 'emailTidakValid', // Tidak pakai @
            'bidang_id'             => $this->bidang->id,
            'role'                  => 'user',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Gagal duplikat NIP / Email
    // Memetakan: Validasi 'unique:users,nip' di StoreUserRequest
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_gagal_jika_nip_dan_email_sudah_terdaftar()
    {
        User::factory()->create([
            'nip'   => '200505132027112030',
            'email' => 'zahra@email.com',
        ]);

        $response = $this->post(route('admin.manage_users.store'), [
            'name'                  => 'Zahra Duplikat',
            'nip'                   => '200505132027112030', // Duplikat
            'email'                 => 'zahra@email.com',   // Duplikat
            'bidang_id'             => $this->bidang->id,
            'role'                  => 'user',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['nip', 'email']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Gagal jika password melebihi batas (max:12)
    // Memetakan: Validasi 'max:12' pada input password
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_akun_gagal_jika_password_melebihi_batas_maksimal()
    {
        $response = $this->post(route('admin.manage_users.store'), [
            'name'                  => 'Test User',
            'nip'                   => '200505132027112032',
            'email'                 => 'user2@email.com',
            'bidang_id'             => $this->bidang->id,
            'role'                  => 'user',
            'password'              => 'passwordyangsangatpanjangsekali123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Update Akun Berhasil
    // Memetakan: PUT Request ke ManageUserController@update
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function edit_akun_berhasil_dengan_data_valid()
    {
        $userEdit = User::factory()->create([
            'nip' => '200505132027112040',
            'name' => 'Nama Lama'
        ]);

        $response = $this->putJson(route('admin.manage_users.update', $userEdit->id), [ // Gunakan putJson
            'name'      => 'Nama Baru Update',
            'nip'       => '200505132027112041',
            'email'     => $userEdit->email,
            'bidang_id' => $this->bidang->id,
            'role'      => 'user',
        ]);

        // Karena controller me-return JSON dengan status 200
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $userEdit->id, 'name' => 'Nama Baru Update']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Delete Akun Berhasil
    // Memetakan: DELETE Request ke ManageUserController@destroy
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function hapus_akun_berhasil_oleh_admin()
    {
        $userHapus = User::factory()->create();

        $response = $this->deleteJson(route('admin.manage_users.destroy', $userHapus->id)); // Gunakan deleteJson

        // Karena controller me-return JSON dengan status 200
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $userHapus->id]);
    }
}
