<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Super: Sub Bagian Perencanaan
        User::create([
            'name' => 'Sub Bagian Perencanaan',
            'email' => 'admin@gmail.com',
            'nip' => '197810162003122008',
            'role' => 'admin',
            'password' => Hash::make('123456'),
            'bidang_id' => null, // <--- ADMIN TIDAK PUNYA BIDANG SPESIFIK
        ]);

        // 2. Bidang Kualitas Hidup Perempuan
        User::create([
            'name' => 'Bidang Kualitas Hidup Perempuan',
            'email' => 'khp@gmail.com',
            'nip' => '196909071999032003',
            'role' => 'user',
            'password' => Hash::make('123456'),
            'bidang_id' => 1, // <--- ID KHP
        ]);

        // 3. Bidang Pemenuhan Hak Anak
        User::create([
            'name' => 'Bidang Pemenuhan Hak Anak',
            'email' => 'pha@gmail.com',
            'nip' => '197512122006041026',
            'role' => 'user',
            'password' => Hash::make('123456'),
            'bidang_id' => 2, // <--- ID PHA
        ]);

        // 4. Bidang Perlindungan Perempuan
        User::create([
            'name' => 'Bidang Perlindungan Perempuan',
            'email' => 'pp@gmail.com',
            'nip' => '198007282006042026',
            'role' => 'user',
            'password' => Hash::make('123456'),
            'bidang_id' => 3, // <--- ID PP
        ]);

        // 5. Bidang Perlindungan Khusus Anak
        User::create([
            'name' => 'Bidang Perlindungan Khusus Anak',
            'email' => 'pka@gmail.com',
            'nip' => '198201112009031004',
            'role' => 'user',
            'password' => Hash::make('123456'),
            'bidang_id' => 4, // <--- ID PKA
        ]);
    }
}
