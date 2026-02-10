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
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // Di bagian rules()
    public function rules(): array
    {
        return [
            'nip' => [
                'required',
                'numeric',          // Hanya angka
                'digits_between:1,20', // Batas panjang 1-20 digit
                'regex:/^\S*$/',    // Tidak boleh ada spasi
            ],
            'password' => [
                'required',
                'string',
                'max:20',           // Batasi password max 20 karakter
            ],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Coba login
        if (! Auth::attempt($this->only('nip', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey()); // Hitung kegagalan

            throw ValidationException::withMessages([
                'nip' => 'NIP atau password salah.', // Pesan error generik (Security Best Practice)
            ]);
        }

        RateLimiter::clear($this->throttleKey()); // Reset jika berhasil
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) { // Batas 5 kali salah
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

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
