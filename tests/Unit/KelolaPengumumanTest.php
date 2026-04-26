<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pengumuman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

/**
 * White-Box Testing: Fitur Kelola Pengumuman
 * Sistem: SIMPEL DP3A
 * Menguji logika internal PengumumanController (AJAX/JSON Request)
 */
class KelolaPengumumanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        /** @var User $admin */
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 1 — Tambah Pengumuman Berhasil
    // Memetakan: POST JSON ke admin.pengumuman.store -> return 201
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pengumuman_berhasil_dengan_data_valid()
    {
        $gambar = UploadedFile::fake()->image('banner.jpg');

        $response = $this->postJson(route('admin.pengumuman.store'), [
            'judul'       => 'Rapat Triwulan Pelaporan Kegiatan',
            'konten'      => 'Dilaksanakan Selasa, 21 April 2026',
            'badge_label' => 'Kegiatan',
            'badge_color' => 'blue',
            'gambar'      => $gambar,
            'is_active'   => true,
        ]);

        // Controller Anda me-return JSON dengan HTTP status 201 (Created)
        $response->assertStatus(201);

        $this->assertDatabaseHas('pengumuman', [
            'judul' => 'Rapat Triwulan Pelaporan Kegiatan',
            'is_active' => true,
            'created_by' => $this->admin->id
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 2 — Gagal jika kosong
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pengumuman_gagal_jika_field_wajib_kosong()
    {
        $response = $this->postJson(route('admin.pengumuman.store'), [
            'judul'       => '',
            'konten'      => '',
            'badge_label' => '',
        ]);

        // JSON validation error me-return status 422
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['judul', 'konten', 'badge_label', 'badge_color', 'gambar']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 3 — Tambah dengan status Tidak Aktif
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function tambah_pengumuman_dengan_status_tidak_aktif()
    {
        $gambar = UploadedFile::fake()->image('banner.jpg');

        $response = $this->postJson(route('admin.pengumuman.store'), [
            'judul'       => 'Info Maintenance Sistem',
            'konten'      => 'Akan ada maintenance hari Sabtu',
            'badge_label' => 'Sistem',
            'badge_color' => 'red',
            'gambar'      => $gambar,
            'is_active'   => false,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pengumuman', [
            'judul'     => 'Info Maintenance Sistem',
            'is_active' => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 4 — Edit Pengumuman Berhasil (Method Spoofing)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function edit_pengumuman_berhasil_dengan_data_valid()
    {
        $pengumuman = Pengumuman::create([
            'judul'       => 'Judul Lama',
            'konten'      => 'Konten Lama',
            'badge_label' => 'Info',
            'badge_color' => 'blue',
            'is_active'   => true,
            'created_by'  => $this->admin->id
        ]);

        // Gunakan postJson dengan '_method' => 'PUT' untuk edit data + file di Laravel
        $response = $this->postJson(route('admin.pengumuman.update', $pengumuman->id), [
            '_method'     => 'PUT',
            'judul'       => 'Info Maintenance Update',
            'konten'      => 'Sabtu-Minggu tidak dapat diakses',
            'badge_label' => 'Sistem',
            'badge_color' => 'red',
            'is_active'   => true,
        ]);

        // Controller update() me-return status 200 (OK)
        $response->assertStatus(200);
        $this->assertDatabaseHas('pengumuman', [
            'id'    => $pengumuman->id,
            'judul' => 'Info Maintenance Update',
            'badge_color' => 'red',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 5 — Edit Pengumuman Gagal (Kosong)
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function edit_pengumuman_gagal_jika_field_wajib_dikosongkan()
    {
        $pengumuman = Pengumuman::create([
            'judul'       => 'Judul Lama',
            'konten'      => 'Konten Lama',
            'badge_label' => 'Info',
            'badge_color' => 'blue',
            'is_active'   => true,
            'created_by'  => $this->admin->id
        ]);

        $response = $this->postJson(route('admin.pengumuman.update', $pengumuman->id), [
            '_method' => 'PUT',
            'judul'   => '',
            'konten'  => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['judul', 'konten', 'badge_label', 'badge_color']);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 6 — Hapus Pengumuman Berhasil
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function hapus_pengumuman_berhasil()
    {
        $pengumuman = Pengumuman::create([
            'judul'       => 'Judul Hapus',
            'konten'      => 'Konten Lama',
            'badge_label' => 'Info',
            'badge_color' => 'blue',
            'is_active'   => true,
            'created_by'  => $this->admin->id
        ]);

        $response = $this->deleteJson(route('admin.pengumuman.destroy', $pengumuman->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('pengumuman', ['id' => $pengumuman->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 7 — Hapus id tidak ada
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function hapus_pengumuman_gagal_jika_id_tidak_ditemukan()
    {
        $response = $this->deleteJson(route('admin.pengumuman.destroy', 99999));
        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────────
    // NO 8 — Ganti Gambar Berhasil
    // ─────────────────────────────────────────────────────────────────
    #[Test]
    public function ganti_gambar_pengumuman_berhasil()
    {
        $pengumuman = Pengumuman::create([
            'judul'       => 'Judul Gambar',
            'konten'      => 'Konten Gambar',
            'badge_label' => 'Info',
            'badge_color' => 'blue',
            'gambar_path' => 'pengumuman/lama.jpg', // Simulasi gambar lama
            'is_active'   => true,
            'created_by'  => $this->admin->id
        ]);

        $gambarBaru = UploadedFile::fake()->image('baru.jpg');

        $response = $this->postJson(route('admin.pengumuman.update', $pengumuman->id), [
            '_method'     => 'PUT',
            'judul'       => $pengumuman->judul,
            'konten'      => $pengumuman->konten,
            'badge_label' => $pengumuman->badge_label,
            'badge_color' => $pengumuman->badge_color,
            'gambar'      => $gambarBaru, // Mengunggah gambar baru
            'is_active'   => true,
        ]);

        $response->assertStatus(200);
        $this->assertNotEquals('pengumuman/lama.jpg', $pengumuman->fresh()->gambar_path);
    }
}
