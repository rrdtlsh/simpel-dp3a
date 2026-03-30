<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\PugKomponen;
use App\Models\PugIndikator;

class PugMasterSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        PugIndikator::truncate();
        PugKomponen::truncate();

        Schema::enableForeignKeyConstraints();

        $k1 = PugKomponen::firstOrCreate(
            ['kode' => 'A'],
            ['nama' => 'PELEMBAGAAN PUG — Kab/Kota', 'urutan' => 1]
        );

        PugIndikator::firstOrCreate(['kode' => '1'], ['komponen_id' => $k1->id, 'nama' => 'Regulasi/Kebijakan tentang Penyelenggaraan PUG', 'urutan' => 1]);
        PugIndikator::firstOrCreate(['kode' => '2'], ['komponen_id' => $k1->id, 'nama' => 'SDM dan Internalisasi PUG', 'urutan' => 2]);
        PugIndikator::firstOrCreate(['kode' => '3'], ['komponen_id' => $k1->id, 'nama' => 'Data Terpilah', 'urutan' => 3]);

        $k2 = PugKomponen::firstOrCreate(
            ['kode' => 'B'],
            ['nama' => 'INTEGRASI PUG DALAM PROSES PEMBANGUNAN — Kab/Kota', 'urutan' => 2]
        );

        PugIndikator::firstOrCreate(['kode' => '4'], ['komponen_id' => $k2->id, 'nama' => 'Perencanaan', 'urutan' => 4]);
        PugIndikator::firstOrCreate(['kode' => '5'], ['komponen_id' => $k2->id, 'nama' => 'Penganggaran', 'urutan' => 5]);
        PugIndikator::firstOrCreate(['kode' => '6'], ['komponen_id' => $k2->id, 'nama' => 'Pelaksanaan', 'urutan' => 6]);
        PugIndikator::firstOrCreate(['kode' => '7'], ['komponen_id' => $k2->id, 'nama' => 'Pemantauan', 'urutan' => 7]);
        PugIndikator::firstOrCreate(['kode' => '8'], ['komponen_id' => $k2->id, 'nama' => 'Evaluasi', 'urutan' => 8]);
        PugIndikator::firstOrCreate(['kode' => '9'], ['komponen_id' => $k2->id, 'nama' => 'Pengawasan', 'urutan' => 9]);
        PugIndikator::firstOrCreate(['kode' => '10'], ['komponen_id' => $k2->id, 'nama' => 'Pelaporan', 'urutan' => 10]);

        $k3 = PugKomponen::firstOrCreate(
            ['kode' => 'C'],
            ['nama' => 'INOVASI', 'urutan' => 3]
        );

        PugIndikator::firstOrCreate(['kode' => '11'], ['komponen_id' => $k3->id, 'nama' => 'Inovasi Penyelenggaraan PUG yang mendukung terwujudnya kesetaraan gender', 'urutan' => 11]);

        echo "✅ Data Master PUG berhasil disemai (Seeded) tanpa duplikasi!\n";
    }
}
