<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nip' => [
                'required',
                'numeric',
                'digits:18',
                'regex:/^\S*$/',
            ],
            'password' => [
                'required',
                'string',
                'max:12',
            ],
        ];
    }

    public function messages()
    {
        return [
            'nip.required' => 'NIP wajib diisi.',
            'nip.numeric' => 'Format NIP harus angka.',
            'nip.digits' => 'NIP harus 18 digit.',
            'password.required' => 'Password wajib diisi.',
            'password.max' => 'Password maksimal 12 karakter.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $nip = $this->input('nip');
        $password = $this->input('password');

        $user = \App\Models\User::where('nip', $nip)->first();

        // NIP SALAH
        if (! $user) {
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'nip' => 'NIP tidak ditemukan.',
            ]);
        }

        // PASSWORD SALAH
        if (! Auth::attempt(['nip' => $nip, 'password' => $password])) {
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'password' => 'Password yang Anda masukkan salah.',
            ]);
        }

        // LOGIN BERHASIL
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        // PAKSA hitung ulang ke 60 detik
        RateLimiter::clear($this->throttleKey());
        RateLimiter::hit($this->throttleKey(), 60);

        throw ValidationException::withMessages([
            'nip' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 60 detik.',
        ]);
    }


    // FUNGSI KHUSUS UNTUK MEMASTIKAN COUNTDOWN 60 DETIK PENUH
    protected function incrementLoginAttempts()
    {
        $key = $this->throttleKey();

        // Cek apakah ini akan menjadi kegagalan ke-3 (karena index dimulai dari 0, attempts >= 2 berarti ini yang ke-3)
        if (RateLimiter::attempts($key) >= 2) {
            // Reset dulu agar waktunya mulai dari 60 detik bersih sekarang
            RateLimiter::clear($key);
            // Isi manual 2 hit
            RateLimiter::hit($key, 60);
            RateLimiter::hit($key, 60);
            // Hit ke-3 (yang mengunci) akan dilakukan di baris bawah
        }

        RateLimiter::hit($key, 60);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('nip')) . '|' . $this->ip());
    }
}
