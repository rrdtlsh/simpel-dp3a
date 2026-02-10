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
            'nip' => '197810162003122008', // NIP Contoh Admin
            'role' => 'admin',
            'password' => Hash::make('123456'),
        ]);

        // 2. Bidang Kualitas Hidup Perempuan
        User::create([
            'name' => 'Bidang Kualitas Hidup Perempuan',
            'nip' => '196909071999032003', // NIP Contoh User 1
            'role' => 'user',
            'password' => Hash::make('123456'),
        ]);

        // 3. Bidang Pemenuhan Hak Anak
        User::create([
            'name' => 'Bidang Pemenuhan Hak Anak',
            'nip' => '197512122006041026',
            'role' => 'user',
            'password' => Hash::make('123456'),
        ]);

        // 4. Bidang Perlindungan Perempuan
        User::create([
            'name' => 'Bidang Perlindungan Perempuan',
            'nip' => '198007282006042026',
            'role' => 'user',
            'password' => Hash::make('123456'),
        ]);

        // 5. Bidang Perlindungan Khusus Anak
        User::create([
            'name' => 'Bidang Perlindungan Khusus Anak',
            'nip' => '198201112009031004',
            'role' => 'user',
            'password' => Hash::make('123456'),
        ]);
    }
}
