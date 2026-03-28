<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        $bidangNames = [
            'Kualitas Hidup Perempuan',  // ID 1
            'Pemenuhan Hak Anak',        // ID 2
            'Perlindungan Perempuan',    // ID 3
            'Perlindungan Khusus Anak',  // ID 4
        ];

        foreach ($bidangNames as $nama) {
            Bidang::firstOrCreate(['nama' => $nama]);
        }
    }
}
