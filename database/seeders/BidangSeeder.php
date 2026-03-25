<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    public function run(): void
    {
        $bidangNames = [
            'Kualitas Hidup Perempuan',
            'Pemenuhan Hak Anak',
            'Perlindungan Perempuan',
            'Perlindungan Khusus Anak',
        ];

        foreach ($bidangNames as $nama) {
            Bidang::firstOrCreate(['nama' => $nama]);
        }

        $bidangs = Bidang::orderBy('id')->get();
        if ($bidangs->isEmpty()) {
            return;
        }

        // Pastikan semua user yang masih kosong bidang_id terisi.
        $usersToFill = User::whereNull('bidang_id')->orderBy('id')->get();
        $count = $bidangs->count();

        foreach ($usersToFill as $i => $user) {
            $user->bidang_id = $bidangs[$i % $count]->id;
            $user->save();
        }
    }
}
