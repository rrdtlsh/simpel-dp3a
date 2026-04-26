<?php

namespace Tests\Unit;

use App\Models\PugIndikator;
use App\Models\PugJawaban;
use App\Models\PugJawabanLampiran;
use App\Models\PugKomponen;
use App\Models\PugPertanyaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * White Box Testing — Fitur: Halaman Evaluasi Pertanyaan PUG (Admin)
 * Controller : EvaluasiPugController
 * Logika yang diuji:
 *   - tambahPertanyaan: validasi indikator_id, kode (regex angka+titik), pertanyaan, skor_maksimal
 *   - updatePertanyaan: if ($adaJawabanTerkunci) → 403 tidak bisa edit
 *   - hapusPertanyaan: selalu bisa dihapus (cascade)
 *   - verifikasi: validasi aksi in:disetujui,ditolak + catatan_admin required min:5
 *   - simpanJawaban (admin): if (status=disetujui) → throw exception tidak bisa ubah
 *   - uploadLampiran: if (total > 10) → 400 batas maksimal
 *   - hapusLampiran: if (jawaban.status=disetujui) → 403
 *   - foreach di index: kalkulasi totalSkor hanya status diisi/disetujui
 */
class HalamanEvaluasiPugAdminTest extends TestCase
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

    private function buatStrukturPug(): array
    {
        $komponen = PugKomponen::create([
            'kode' => '2',
            'nama' => 'SDM dan Internalisasi PUG',
            'level' => 'Kab/Kota',
            'urutan' => 2,
            'aktif' => true,
        ]);
        $indikator = PugIndikator::create([
            'komponen_id' => $komponen->id,
            'kode' => '2.1',
            'nama' => 'Indikator SDM',
            'urutan' => 1,
            'aktif' => true,
        ]);
        return [$komponen, $indikator];
    }

    private function buatPertanyaan(PugIndikator $indikator, string $kode = '2.1'): PugPertanyaan
    {
        return PugPertanyaan::create([
            'indikator_id'    => $indikator->id,
            'kode'            => $kode,
            'pertanyaan'      => 'Apakah Pemda memiliki SDM terlatih PUG?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [
                ['label' => 'Ya', 'skor' => 30],
                ['label' => 'Tidak Semua', 'skor' => 15],
                ['label' => 'Tidak Sama Sekali', 'skor' => 0],
            ],
            'urutan' => 1,
            'aktif' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Validasi: gagal tambah pertanyaan jika indikator_id tidak ada
    // Memetakan: 'indikator_id' => ['required', 'exists:pug_indikator,id']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pertanyaan_gagal_jika_indikator_tidak_ditemukan()
    {
        $this->loginSebagaiAdmin();

        $response = $this->postJson(route('evaluasi-pug.pertanyaan.tambah'), [
            'indikator_id'    => 999, // tidak ada
            'kode'            => '2.1',
            'pertanyaan'      => 'Pertanyaan valid?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [['label' => 'Ya', 'skor' => 30]],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.indikator_id', fn($v) => count($v) > 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Validasi regex kode: gagal jika kode mengandung huruf
    // Memetakan: 'kode' => ['regex:/^[0-9.]+$/'] → 'Kode hanya boleh berisi angka dan titik'
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pertanyaan_gagal_jika_kode_mengandung_huruf()
    {
        $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();

        $response = $this->postJson(route('evaluasi-pug.pertanyaan.tambah'), [
            'indikator_id'    => $indikator->id,
            'kode'            => 'A.1', // mengandung huruf
            'pertanyaan'      => 'Pertanyaan valid?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [['label' => 'Ya', 'skor' => 30]],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.kode', fn($v) => count($v) > 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Validasi: gagal jika skor_maksimal kosong (required)
    // Memetakan: 'skor_maksimal' => ['required', 'numeric', 'min:0', 'max:100']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pertanyaan_gagal_jika_skor_maksimal_kosong()
    {
        $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();

        $response = $this->postJson(route('evaluasi-pug.pertanyaan.tambah'), [
            'indikator_id'    => $indikator->id,
            'kode'            => '2.2',
            'pertanyaan'      => 'Pertanyaan valid?',
            // skor_maksimal sengaja dihilangkan
            'pilihan_jawaban' => [['label' => 'Ya', 'skor' => 30]],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.skor_maksimal', fn($v) => count($v) > 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Validasi: gagal jika pilihan_jawaban kosong (required|array|min:1)
    // Memetakan: 'pilihan_jawaban' => ['required', 'array', 'min:1']
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pertanyaan_gagal_jika_pilihan_jawaban_kosong()
    {
        $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();

        $response = $this->postJson(route('evaluasi-pug.pertanyaan.tambah'), [
            'indikator_id'    => $indikator->id,
            'kode'            => '2.2',
            'pertanyaan'      => 'Pertanyaan valid?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [], // kosong
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.pilihan_jawaban', fn($v) => count($v) > 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Berhasil tambah pertanyaan dengan data valid
    // Memetakan: PugPertanyaan::create([...]) → return 200
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pertanyaan_berhasil_dengan_data_valid()
    {
        $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();

        $response = $this->postJson(route('evaluasi-pug.pertanyaan.tambah'), [
            'indikator_id'    => $indikator->id,
            'kode'            => '2.1',
            'pertanyaan'      => 'Apakah Pemda memiliki SDM terlatih PUG?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [
                ['label' => 'Ya', 'skor' => 30],
                ['label' => 'Tidak', 'skor' => 0],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('pug_pertanyaan', ['kode' => '2.1']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Logika if: pertanyaan tidak bisa diedit jika ada jawaban disetujui/ditolak
    // Memetakan: if ($adaJawabanTerkunci) → return 403
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function edit_pertanyaan_gagal_jika_sudah_ada_jawaban_disetujui()
    {
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        // Buat jawaban dengan status disetujui
        PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'disetujui',
            'diisi_oleh' => $admin->id,
        ]);

        $response = $this->putJson(route('evaluasi-pug.pertanyaan.update', $pertanyaan->id), [
            'indikator_id'    => $indikator->id,
            'kode'            => '2.2',
            'pertanyaan'      => 'Pertanyaan yang diubah?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [['label' => 'Ya', 'skor' => 30]],
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Berhasil edit pertanyaan jika belum ada jawaban terkunci
    // Memetakan: $pertanyaan->update([...]) → return 200
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function edit_pertanyaan_berhasil_jika_belum_ada_jawaban_terkunci()
    {
        $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator, '2.1');

        $response = $this->putJson(route('evaluasi-pug.pertanyaan.update', $pertanyaan->id), [
            'indikator_id'    => $indikator->id,
            'kode'            => '2.2', // kode diubah dari 2.1 menjadi 2.2
            'pertanyaan'      => 'Apakah Pemda memiliki SDM terlatih PUG?',
            'skor_maksimal'   => 30,
            'pilihan_jawaban' => [['label' => 'Ya', 'skor' => 30]],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('pug_pertanyaan', ['id' => $pertanyaan->id, 'kode' => '2.2']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Hapus pertanyaan selalu berhasil (cascade ke jawaban)
    // Memetakan: PugPertanyaan::findOrFail($id)->delete() → return 200
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function hapus_pertanyaan_berhasil_dan_jawaban_ikut_terhapus()
    {
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        // Tambahkan jawaban agar bisa diverifikasi cascade delete
        PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'diisi',
            'diisi_oleh' => $admin->id,
        ]);

        $response = $this->deleteJson(route('evaluasi-pug.pertanyaan.destroy', $pertanyaan->id));

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Pertanyaan dan jawaban harus terhapus (cascade)
        $this->assertDatabaseMissing('pug_pertanyaan', ['id' => $pertanyaan->id]);
        $this->assertDatabaseMissing('pug_jawaban', ['pertanyaan_id' => $pertanyaan->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 9 — Verifikasi: gagal jika catatan_admin kurang dari 5 karakter (min:5)
    // Memetakan: 'catatan_admin' => ['min:5'] → error validasi
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function verifikasi_gagal_jika_catatan_admin_kurang_dari_5_karakter()
    {
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        $jawaban = PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'diisi',
            'diisi_oleh' => $admin->id,
        ]);

        $response = $this->postJson(route('evaluasi-pug.verifikasi'), [
            'jawaban_id'    => $jawaban->id,
            'aksi'          => 'disetujui',
            'catatan_admin' => 'Ok', // hanya 2 karakter, kurang dari 5
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.catatan_admin', fn($v) => count($v) > 0);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 10 — Verifikasi berhasil: jawaban disetujui → status berubah
    // Memetakan: $jawaban->update(['status' => 'disetujui', ...])
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function verifikasi_berhasil_menyetujui_jawaban()
    {
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        $jawaban = PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'diisi',
            'diisi_oleh' => $admin->id,
        ]);

        $response = $this->postJson(route('evaluasi-pug.verifikasi'), [
            'jawaban_id'    => $jawaban->id,
            'aksi'          => 'disetujui',
            'catatan_admin' => 'Format dan ketentuan dokumen sudah valid.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('pug_jawaban', ['id' => $jawaban->id, 'status' => 'disetujui']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 11 — Upload lampiran: gagal jika total file melebihi 10
    // Memetakan: if ($jumlahSaatIni + $jumlahUpload > 10) → return 400
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function upload_lampiran_gagal_jika_total_file_melebihi_10()
    {
        Storage::fake('public');
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        $jawaban = PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'diisi',
            'diisi_oleh' => $admin->id,
        ]);

        // Buat 10 lampiran yang sudah ada
        for ($i = 1; $i <= 10; $i++) {
            PugJawabanLampiran::create([
                'jawaban_id'    => $jawaban->id,
                'nama_file'     => "file_{$i}.pdf",
                'path_file'     => "pug_lampiran/file_{$i}.pdf",
                'mime_type'     => 'application/pdf',
                'ukuran'        => 1024,
                'diupload_oleh' => $admin->id,
            ]);
        }

        // Coba upload 1 file lagi (total jadi 11)
        $response = $this->postJson(route('evaluasi-pug.lampiran.upload'), [
            'pertanyaan_id' => $pertanyaan->id,
            'tahun'         => 2025,
            'file'          => [UploadedFile::fake()->create('extra.pdf', 100, 'application/pdf')],
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 12 — Hapus lampiran gagal jika jawaban sudah disetujui
    // Memetakan: if ($lampiran->jawaban->status === 'disetujui') → 403
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function hapus_lampiran_gagal_jika_jawaban_sudah_disetujui()
    {
        Storage::fake('public');
        $admin = $this->loginSebagaiAdmin();
        [, $indikator] = $this->buatStrukturPug();
        $pertanyaan = $this->buatPertanyaan($indikator);

        $jawaban = PugJawaban::create([
            'pertanyaan_id' => $pertanyaan->id,
            'tahun' => 2025,
            'jawaban_kode' => 'ya',
            'jawaban_label' => 'Ya',
            'skor' => 30,
            'status' => 'disetujui', // sudah disetujui
            'diisi_oleh' => $admin->id,
        ]);

        $lampiran = PugJawabanLampiran::create([
            'jawaban_id' => $jawaban->id,
            'nama_file' => 'bukti.pdf',
            'path_file' => 'pug_lampiran/bukti.pdf',
            'mime_type' => 'application/pdf',
            'ukuran' => 1024,
            'diupload_oleh' => $admin->id,
        ]);

        $response = $this->deleteJson(route('evaluasi-pug.lampiran.hapus', $lampiran->id));

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }
}
