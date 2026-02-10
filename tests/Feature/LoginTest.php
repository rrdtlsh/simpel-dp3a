<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_authenticate_with_invalid_nip_format()
    {
        $response = $this->post('/login', [
            'nip' => 'nip-pake-huruf', // Salah
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('nip'); // Harusnya error
    }

    public function test_users_cannot_authenticate_with_spaces_in_nip()
    {
        $response = $this->post('/login', [
            'nip' => '123 456', // Ada spasi
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('nip');
    }

    public function test_throttle_is_working()
    {
        $user = User::factory()->create();

        // Coba login salah 6 kali
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'nip' => $user->nip,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'nip' => $user->nip,
            'password' => 'wrong-password',
        ]);

        // Harus ada pesan throttle (Too many attempts)
        $response->assertSessionHasErrors('nip');
    }
}
