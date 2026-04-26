<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class PengajuanLogicTest extends TestCase
{
    /**
     * Logika Simulasi: Mengecek apakah pengajuan sudah melewati deadline (overdue)
     */
    private function checkIsOverdue($dueDate)
    {
        $now = Carbon::now();
        if ($now->greaterThan($dueDate)) {
            return "Terlambat";
        }
        return "Masih Aktif";
    }

    /**
     * Test Case 1: Pengujian Jika Deadline Sudah Lewat (Jalur Logika If pertama)
     */
    public function test_logika_pengajuan_terlambat()
    {
        // Arrange: Kita buat waktu deadline adalah hari H minus 1 (kemarin)
        $kemarin = Carbon::now()->subDay();

        // Act: Jalankan fungsi logika
        $hasil = $this->checkIsOverdue($kemarin);

        // Assert: Kita pastikan sistem menjawab "Terlambat"
        $this->assertEquals("Terlambat", $hasil);
    }

    /**
     * Test Case 2: Pengujian Jika Deadline Belum Lewat (Jalur Logika Else)
     */
    public function test_logika_pengajuan_masih_aktif()
    {
        // Arrange: Kita buat waktu deadline adalah hari H plus 1 (besok)
        $besok = Carbon::now()->addDay();

        // Act: Jalankan fungsi logika
        $hasil = $this->checkIsOverdue($besok);

        // Assert: Kita pastikan sistem menjawab "Masih Aktif"
        $this->assertEquals("Masih Aktif", $hasil);
    }
}
