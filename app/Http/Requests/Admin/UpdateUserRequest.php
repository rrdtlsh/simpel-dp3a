<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('manage_user'); // nama parameter route resource

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'password'  => ['nullable', 'string', 'min:6', 'confirmed'],
            'bidang_id' => ['nullable', 'exists:bidangs,id'],
            'role'      => ['required', 'in:admin,user'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email ini sudah digunakan oleh akun lain.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'bidang_id.exists'   => 'Bidang yang dipilih tidak ditemukan.',
            'role.required'      => 'Peran (role) wajib dipilih.',
        ];
    }
}
