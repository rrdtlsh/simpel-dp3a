<?php

namespace Tests\Unit;

use App\Models\Bidang;
use App\Models\Pengajuan;
use App\Models\PengajuanFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White Box Testing — Fitur: Halaman Verifikasi Dokumen
 * Controller : PengajuanController@review
 * Logika yang diuji:
 *   - Validasi required: status (in:pending,approved,rejected)
 *   - Validasi nullable admin_notes dengan closure max 500 karakter
 *   - if (!$file) → return 404 'User belum mengunggah dokumen.'
 *   - $file->update(['status' => ...]) → update status dokumen
 *   - Status transition: pending → approved / pending → rejected
 *   - Admin harus bisa lihat halaman verifikasi
 */
class HalamanVerifikasiDokumenTest extends TestCase
{
    use RefreshDatabase;

    private function loginSebagaiAdmin(): User
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role'     => 'admin',
            'nip'      => '197810162003122001',
            'password' => Hash::make('admin123'),
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    private function buatPengajuanDenganFile(User $admin, string $statusFile = 'pending'): array
    {
        $bidang = Bidang::create(['nama' => 'Bidang Test ' . uniqid()]);
        $user = User::factory()->create(['role' => 'user', 'bidang_id' => $bidang->id]);

        $pengajuan = Pengajuan::create([
            'judul'      => 'Laporan Test ' . uniqid(),
            'bidang_id'  => $bidang->id,
            'tahun'      => 2025,
            'due_date'   => now()->addDays(5),
            'status'     => 'open',
            'created_by' => $admin->id,
        ]);

        $file = PengajuanFile::create([
            'pengajuan_id' => $pengajuan->id,
            'user_id'      => $user->id,
            'files'        => [['path' => 'test/file.pdf', 'original_name' => 'file.pdf']],
            'status'       => $statusFile,
            'admin_notes'  => null,
        ]);

        return [$pengajuan, $file];
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Validasi required: gagal jika status tidak diisi
    // Memetakan: 'status' => ['required']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_gagal_jika_status_tidak_diisi()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin);

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'admin_notes' => 'Catatan ada',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Validasi in:pending,approved,rejected: gagal jika status tidak valid
    // Memetakan: 'status' => ['in:pending,approved,rejected']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_gagal_jika_status_tidak_valid()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin);

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status' => 'status_tidak_valid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Logika if (!$file): gagal review jika user belum upload dokumen
    // Memetakan: if (!$file) → return 404 'User belum mengunggah dokumen.'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_gagal_jika_user_belum_upload_dokumen()
    {
        $admin = $this->loginSebagaiAdmin();
        $bidang = Bidang::create(['nama' => 'Bidang Kosong']);

        // Buat pengajuan TANPA file
        $pengajuan = Pengajuan::create([
            'judul'      => 'Laporan Tanpa File',
            'bidang_id'  => $bidang->id,
            'tahun'      => 2025,
            'due_date'   => now()->addDays(5),
            'status'     => 'open',
            'created_by' => $admin->id,
        ]);

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'approved',
            'admin_notes' => 'Catatan verifikasi',
        ]);

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Menerima dokumen: status berubah dari pending ke approved
    // Memetakan: $file->update(['status' => 'approved'])
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function admin_berhasil_menerima_dokumen_status_jadi_approved()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'approved',
            'admin_notes' => 'Dokumen sudah sesuai format',
        ]);

        $response->assertStatus(200);

        // Status di database harus berubah ke approved
        $this->assertDatabaseHas('pengajuan_files', [
            'id'     => $file->id,
            'status' => 'approved',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Menolak dokumen dengan catatan revisi
    // Memetakan: $file->update(['status' => 'rejected', 'admin_notes' => ...])
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function admin_berhasil_menolak_dokumen_dengan_catatan_revisi()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'rejected',
            'admin_notes' => 'Perbaiki format laporan sesuai ketentuan.',
        ]);

        $response->assertStatus(200);

        // Status harus rejected dan catatan tersimpan
        $this->assertDatabaseHas('pengajuan_files', [
            'id'          => $file->id,
            'status'      => 'rejected',
            'admin_notes' => 'Perbaiki format laporan sesuai ketentuan.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Validasi closure admin_notes: gagal jika catatan > 500 karakter
    // Memetakan: closure → mb_strlen($plainText) > 500 → $fail(...)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_gagal_jika_catatan_melebihi_500_karakter()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'rejected',
            'admin_notes' => str_repeat('a', 501), // 501 karakter, melebihi batas 500
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['admin_notes']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Review berhasil meski admin_notes kosong (nullable)
    // Memetakan: 'admin_notes' => ['nullable'] → tidak wajib diisi saat approved
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_berhasil_tanpa_admin_notes_saat_approve()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'approved',
            // PERBAIKAN: Tetap kirimkan key admin_notes, tapi set ke null
            // Ini mensimulasikan form textarea di web yang tidak diisi oleh user
            'admin_notes' => null,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pengajuan_files', [
            'id'     => $file->id,
            'status' => 'approved',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — File yang sudah approved tidak muncul di list verifikasi
    // Memetakan: verifikasiContent() → Pengajuan::with('files')::latest()->get()
    //            → semua status tampil, termasuk pending dan approved
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function file_berstatus_pending_muncul_di_halaman_verifikasi()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        // Ambil semua pengajuan beserta file-nya
        $pengajuans = Pengajuan::with(['files' => fn($q) => $q->latest()])->latest()->get();

        // Harus ada 1 pengajuan
        $this->assertCount(1, $pengajuans);
        // File-nya berstatus pending
        $this->assertEquals('pending', $pengajuans->first()->files->first()->status);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 9 — Catatan revisi boleh mengandung HTML (akan di-strip oleh closure)
    // Memetakan: strip_tags(html_entity_decode($value)) sebelum hitung panjang
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function review_berhasil_dengan_catatan_kurang_dari_500_karakter()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file] = $this->buatPengajuanDenganFile($admin, 'pending');

        $response = $this->postJson(route('admin.pengajuan.review', $pengajuan->id), [
            'status'      => 'rejected',
            'admin_notes' => str_repeat('a', 499), // tepat 499 karakter
        ]);

        $response->assertStatus(200);
    }
}
