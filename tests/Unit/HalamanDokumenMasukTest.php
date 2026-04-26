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
 * White Box Testing — Fitur: Halaman Dokumen Masuk
 * Controller : PengajuanController@dokumenMasukContent, exportPdf, exportExcel
 * Logika yang diuji:
 *   - whereHas('files', fn → status = 'approved') → hanya dokumen approved
 *   - if ($request->bidang) → filter berdasarkan bidang
 *   - if ($request->tahun) → filter berdasarkan tahun
 *   - foreach ($pengajuans as ...) → loop untuk membangun data export
 *   - Export PDF menghasilkan Content-Type: application/pdf
 *   - Export Excel menghasilkan Content-Type: application/xlsx
 */
class HalamanDokumenMasukTest extends TestCase
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

    private function buatPengajuanApproved(User $admin, string $namaBidang = 'Perlindungan Perempuan', int $tahun = 2025): array
    {
        $bidang = Bidang::firstOrCreate(['nama' => $namaBidang]);
        $user = User::factory()->create(['role' => 'user', 'bidang_id' => $bidang->id]);

        $pengajuan = Pengajuan::create([
            'judul'      => 'Laporan Test ' . uniqid(),
            'bidang_id'  => $bidang->id,
            'tahun'      => $tahun,
            'due_date'   => now()->addDays(10),
            'status'     => 'open',
            'created_by' => $admin->id,
        ]);

        $file = PengajuanFile::create([
            'pengajuan_id' => $pengajuan->id,
            'user_id'      => $user->id,
            'files'        => [['path' => 'test/file.pdf', 'original_name' => 'laporan.pdf']],
            'status'       => 'approved',
            'admin_notes'  => null,
        ]);

        return [$pengajuan, $file, $bidang];
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Logika whereHas: hanya dokumen dengan status approved yang masuk
    // Memetakan: whereHas('files', fn → status = 'approved')
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function dokumen_masuk_hanya_menampilkan_dokumen_berstatus_approved()
    {
        $admin = $this->loginSebagaiAdmin();

        $this->buatPengajuanApproved($admin); // ini approved

        // Buat juga dokumen pending (tidak seharusnya muncul)
        $bidang2 = Bidang::firstOrCreate(['nama' => 'Bidang Pending']);
        $user2 = User::factory()->create(['role' => 'user', 'bidang_id' => $bidang2->id]);
        $pend = Pengajuan::create([
            'judul' => 'Laporan Pending',
            'bidang_id' => $bidang2->id,
            'tahun' => 2025,
            'due_date' => now()->addDays(5),
            'status' => 'open',
            'created_by' => $admin->id,
        ]);
        PengajuanFile::create([
            'pengajuan_id' => $pend->id,
            'user_id' => $user2->id,
            'files' => [['path' => 'x.pdf', 'original_name' => 'x.pdf']],
            'status' => 'pending',
        ]);

        // Kueri dokumen masuk (hanya approved)
        $hasil = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))->get();

        // Hanya 1 yang approved
        $this->assertCount(1, $hasil);
        $this->assertEquals('approved', $hasil->first()->files->first()->status);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Dokumen pending tidak masuk ke halaman Dokumen Masuk
    // Memetakan: file dengan status = 'pending' tidak lolos whereHas
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function dokumen_pending_tidak_muncul_di_dokumen_masuk()
    {
        $admin = $this->loginSebagaiAdmin();
        $bidang = Bidang::create(['nama' => 'Bidang Pending']);
        $user = User::factory()->create(['role' => 'user', 'bidang_id' => $bidang->id]);

        $pengajuan = Pengajuan::create([
            'judul' => 'Laporan Masih Pending',
            'bidang_id' => $bidang->id,
            'tahun' => 2025,
            'due_date' => now()->addDays(5),
            'status' => 'open',
            'created_by' => $admin->id,
        ]);
        PengajuanFile::create([
            'pengajuan_id' => $pengajuan->id,
            'user_id' => $user->id,
            'files' => [['path' => 'x.pdf', 'original_name' => 'x.pdf']],
            'status' => 'pending',
        ]);

        $hasil = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))->get();

        $this->assertEmpty($hasil);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Logika if ($request->bidang): filter berdasarkan nama bidang
    // Memetakan: exportPdf/exportExcel → if ($request->bidang) → whereHas(bidang)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function filter_berdasarkan_bidang_memfilter_dengan_benar()
    {
        $admin = $this->loginSebagaiAdmin();

        $this->buatPengajuanApproved($admin, 'Perlindungan Perempuan', 2025);
        $this->buatPengajuanApproved($admin, 'Kualitas Hidup', 2025);

        // Filter: hanya Perlindungan Perempuan
        $hasil = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))
            ->whereHas('bidang', fn($q) => $q->where('nama', 'Perlindungan Perempuan'))
            ->get();

        $this->assertCount(1, $hasil);
        $this->assertEquals('Perlindungan Perempuan', $hasil->first()->bidang->nama);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Logika if ($request->tahun): filter berdasarkan tahun
    // Memetakan: exportPdf/exportExcel → if ($request->tahun) → whereYear(...)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function filter_berdasarkan_tahun_memfilter_dengan_benar()
    {
        $admin = $this->loginSebagaiAdmin();

        $this->buatPengajuanApproved($admin, 'Bidang A', 2024);
        $this->buatPengajuanApproved($admin, 'Bidang B', 2025);

        // Filter: hanya tahun 2025
        $hasil = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))
            ->where('tahun', 2025)
            ->get();

        $this->assertCount(1, $hasil);
        $this->assertEquals(2025, $hasil->first()->tahun);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Logika foreach: data export memiliki kolom yang benar
    // Memetakan: foreach ($pengajuans as $index => $row) → $data[] = [...]
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function data_export_mengandung_kolom_yang_benar()
    {
        $admin = $this->loginSebagaiAdmin();
        [$pengajuan, $file, $bidang] = $this->buatPengajuanApproved($admin, 'Test Bidang', 2025);

        $pengajuans = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))
            ->with(['bidang', 'files' => fn($q) => $q->where('status', 'approved')->latest()])
            ->get();

        // Simulasi logika foreach dalam controller
        $data = [];
        foreach ($pengajuans as $index => $row) {
            $fileData = $row->files->first();
            $data[] = [
                $index + 1,
                $row->judul,
                $row->bidang->nama ?? '-',
                $row->tahun ?? '-',
                $fileData ? $fileData->updated_at->format('d M Y H:i') : '-',
            ];
        }

        // Harus ada 1 baris data dengan 5 kolom
        $this->assertCount(1, $data);
        $this->assertCount(5, $data[0]);
        $this->assertEquals('Test Bidang', $data[0][2]); // kolom ke-3 adalah nama bidang
        $this->assertEquals(2025, $data[0][3]);          // kolom ke-4 adalah tahun
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Export PDF dokumen masuk admin menghasilkan file PDF
    // Memetakan: Pdf::loadView(...)->download('...pdf') → Content-Type: application/pdf
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function export_pdf_dokumen_masuk_menghasilkan_file_pdf()
    {
        $admin = $this->loginSebagaiAdmin();
        $this->buatPengajuanApproved($admin);

        $response = $this->get(route('admin.export.pdf'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Export Excel dokumen masuk admin menghasilkan file Excel
    // Memetakan: Excel::download(...) → Content-Type: spreadsheetml
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function export_excel_dokumen_masuk_menghasilkan_file_excel()
    {
        $admin = $this->loginSebagaiAdmin();
        $this->buatPengajuanApproved($admin);

        $response = $this->get(route('admin.export.excel'));

        $response->assertStatus(200);
        // Excel menggunakan Content-Type spreadsheetml
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('Content-Type')
        );
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Search dokumen yang tidak ada mengembalikan koleksi kosong
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function search_dokumen_masuk_yang_tidak_ada_mengembalikan_kosong()
    {
        $this->loginSebagaiAdmin();

        $hasil = Pengajuan::whereHas('files', fn($q) => $q->where('status', 'approved'))
            ->where('judul', 'like', '%Laporan Survei%')
            ->get();

        $this->assertEmpty($hasil);
    }
}
