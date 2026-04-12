<?php
// ============================================================
// FILE 1: app/Http/Requests/Admin/StoreUserRequest.php
// ============================================================
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:6', 'confirmed'],
            'bidang_id' => ['nullable', 'exists:bidangs,id'],
            'role'      => ['required', 'in:admin,user'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'name.max'           => 'Nama maksimal 255 karakter.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'bidang_id.exists'   => 'Bidang yang dipilih tidak ditemukan.',
            'role.required'      => 'Peran (role) wajib dipilih.',
            'role.in'            => 'Peran tidak valid.',
        ];
    }
}
