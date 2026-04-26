<?php

namespace Database\Factories;

use App\Models\Bidang;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => fake()->name(),
            'nip'       => fake()->numerify('##################'), // 18 digit angka
            'email'     => fake()->unique()->safeEmail(),
            'password'  => Hash::make('password123'),
            'role'      => 'user',
            'bidang_id' => null,
        ];
    }

    // State khusus untuk admin
    public function admin(): static
    {
        return $this->state(['role' => 'admin']);
    }

    // State khusus untuk super admin
    public function superAdmin(): static
    {
        return $this->state(['role' => 'super_admin']);
    }
}
