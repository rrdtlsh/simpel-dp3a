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
            'password.required' => 'Password wajib diisi.',
            'nip.digits' => 'NIP harus 18 digit.',
            'nip.numeric' => 'Nama dan Password yang dimasukkan salah.',
            'nip.regex' => 'Nama dan Password yang dimasukkan salah.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Cek apakah NIP ada di database dulu (untuk pesan error spesifik)
        $user = \App\Models\User::where('nip', $this->input('nip'))->first();

        if (! $user) {
            // Jika NIP tidak ditemukan, hitung sebagai percobaan gagal
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'nip' => 'NIP tidak ditemukan.', // Pesan khusus NIP salah
            ]);
        }

        if (! Auth::attempt($this->only('nip', 'password'), $this->boolean('remember'))) {
            // Jika NIP ada tapi password salah
            RateLimiter::hit($this->throttleKey(), 60);

            throw ValidationException::withMessages([
                'password' => 'Password yang Anda masukkan salah.', // Pesan khusus Password salah
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        // Ubah batas percobaan: 3 kali, delay 60 detik (1 menit)
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'nip' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('nip')) . '|' . $this->ip());
    }
}
