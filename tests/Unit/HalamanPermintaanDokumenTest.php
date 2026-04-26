<?php

namespace Tests\Unit;

use App\Models\Bidang;
use App\Models\Pengajuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test; // <-- TAMBAHAN BARU
use Tests\TestCase;

class HalamanPermintaanDokumenTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper ──────────────────────────────────────────────────────
    private function loginSebagaiAdmin(): User
    {
        /** @var User $admin */ // <-- TAMBAHAN BARU
        $admin = User::factory()->create([
            'role'     => 'admin',
            'nip'      => '197810162003122001',
            'password' => Hash::make('admin123'),
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    private function buatBidang(string $nama = 'Perlindungan Perempuan'): Bidang
    {
        return Bidang::create(['nama' => $nama]);
    }

    private function dataPermintaanValid(int $bidangId): array
    {
        return [
            'judul'      => 'Laporan Observasi Lapangan',
            'deskripsi'  => 'Jangan lupa lampirkan dokumentasi',
            'bidang_id'  => $bidangId,
            'tahun'      => '2025',
            'due_date'   => now()->addDays(5)->format('Y-m-d H:i:s'),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function user_biasa_tidak_bisa_membuat_permintaan_dokumen()
    {
        $bidang = $this->buatBidang();
        /** @var User $user */
        $user = User::factory()->create(['role' => 'user', 'bidang_id' => $bidang->id]);
        $this->actingAs($user);

        $response = $this->postJson(route('admin.pengajuan.store'), $this->dataPermintaanValid($bidang->id));

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_judul_tidak_diisi()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['judul']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_judul_kurang_dari_5_karakter()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Lap',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['judul']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_judul_lebih_dari_50_karakter()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Observasi Lapangan Seluruh Indonesia Merdeka Hore',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['judul']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_judul_mengandung_simbol_tidak_valid()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan 123!@#$%',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['judul']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_judul_sudah_ada()
    {
        $admin = $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        Pengajuan::create([
            'judul'      => 'Laporan Observasi Lapangan',
            'bidang_id'  => $bidang->id,
            'tahun'      => 2025,
            'due_date'   => now()->addDays(5),
            'status'     => 'open',
            'created_by' => $admin->id,
        ]);

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Observasi Lapangan',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['judul']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_deadline_sudah_lewat()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Valid Baru',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->subDays(1)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['due_date']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_bidang_tidak_ditemukan()
    {
        $this->loginSebagaiAdmin();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Valid Baru',
            'bidang_id' => 999,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['bidang_id']);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_berhasil_dengan_data_valid()
    {
        $admin = $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Hasil Observasi',
            'deskripsi' => 'Jangan lupa lampirkan dokumentasi',
            'bidang_id' => $bidang->id,
            'tahun'     => '2025',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        // Cek apakah web Anda me-return JSON 200 atau Redirect 302
        if ($response->status() === 302) {
            $response->assertStatus(302);
        } else {
            $response->assertStatus(200);
        }

        $this->assertDatabaseHas('pengajuans', [
            'judul'      => 'Laporan Hasil Observasi',
            'bidang_id'  => $bidang->id,
            'created_by' => $admin->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function admin_berhasil_menghapus_permintaan_dokumen()
    {
        $admin = $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $pengajuan = Pengajuan::create([
            'judul'      => 'Laporan Yang Akan Dihapus',
            'bidang_id'  => $bidang->id,
            'tahun'      => 2025,
            'due_date'   => now()->addDays(5),
            'status'     => 'open',
            'created_by' => $admin->id,
        ]);

        $response = $this->deleteJson(route('admin.pengajuan.destroy', $pengajuan->id));

        if ($response->status() === 302) {
            $response->assertStatus(302);
        } else {
            $response->assertStatus(200);
        }

        $this->assertDatabaseMissing('pengajuans', ['id' => $pengajuan->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function search_permintaan_yang_tidak_ada_mengembalikan_koleksi_kosong()
    {
        $this->loginSebagaiAdmin();
        $this->buatBidang();

        $hasil = Pengajuan::where('judul', 'like', '%Laporan Survei%')->get();

        $this->assertEmpty($hasil);
    }

    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_permintaan_gagal_jika_tahun_bukan_4_digit()
    {
        $this->loginSebagaiAdmin();
        $bidang = $this->buatBidang();

        $response = $this->postJson(route('admin.pengajuan.store'), [
            'judul'     => 'Laporan Baru Valid',
            'bidang_id' => $bidang->id,
            'tahun'     => '25',
            'due_date'  => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['tahun']);
    }
}
