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
            // Panggil fungsi khusus Anda di sini!
            $this->incrementLoginAttempts();

            throw ValidationException::withMessages([
                'nip' => 'NIP tidak ditemukan.',
            ]);
        }

        // PASSWORD SALAH
        if (! Auth::attempt(['nip' => $nip, 'password' => $password])) {
            // Panggil fungsi khusus Anda di sini!
            $this->incrementLoginAttempts();

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

        event(new Lockout($this));

        // Teks 60 detik persis seperti keinginan Anda
        throw ValidationException::withMessages([
            'nip' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 60 detik.',
        ]);
    }

    // FUNGSI KHUSUS ANDA YANG SUDAH TERHUBUNG
    protected function incrementLoginAttempts()
    {
        $key = $this->throttleKey();

        if (RateLimiter::attempts($key) >= 2) {
            RateLimiter::clear($key);
            RateLimiter::hit($key, 60);
            RateLimiter::hit($key, 60);
        }

        RateLimiter::hit($key, 60);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('nip')) . '|' . $this->ip());
    }
}
