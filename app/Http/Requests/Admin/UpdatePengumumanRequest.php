<?php

// FILE: app/Http/Requests/Admin/UpdatePengumumanRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ID pengumuman yang sedang diedit — abaikan saat cek unique
        $pengumumanId = $this->route('pengumuman')?->id;

        return [
            'judul'       => [
                'required',
                'string',
                'max:100',
                Rule::unique('pengumuman', 'judul')->ignore($pengumumanId),
            ],
            'konten'      => ['required', 'string', 'max:1000'],
            'gambar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'badge_label' => ['required', 'string', 'max:50'],
            'badge_color' => ['required', 'in:red,blue,green'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required'       => 'Judul pengumuman wajib diisi.',
            'judul.max'            => 'Judul maksimal 100 karakter.',
            'judul.unique'         => 'Judul ini sudah digunakan, gunakan judul yang berbeda.',
            'konten.required'      => 'Konten pengumuman wajib diisi.',
            'konten.max'           => 'Konten maksimal 1000 karakter.',
            'gambar.image'         => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes'         => 'Format gambar hanya boleh: JPG, JPEG, atau PNG.',
            'gambar.max'           => 'Ukuran gambar maksimal 2 MB.',
            'badge_label.required' => 'Label badge wajib diisi.',
            'badge_label.max'      => 'Label badge maksimal 50 karakter.',
            'badge_color.required' => 'Warna badge wajib dipilih.',
            'badge_color.in'       => 'Warna badge tidak valid.',
        ];
    }
}
