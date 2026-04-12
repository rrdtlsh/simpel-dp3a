<?php

// FILE: app/Http/Requests/Admin/StorePengumumanRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => [
                'required',
                'string',
                'max:100',
                'unique:pengumuman,judul',
                function ($attribute, $value, $fail) {
                    // Tambahkan pengecekan jumlah data di sini
                    if (\App\Models\Pengumuman::count() >= 6) {
                        $fail('Batas maksimal pengumuman adalah 6 data. Hapus data lama untuk menambah baru.');
                    }
                },
            ],
            'konten'      => ['required', 'string', 'max:1000'],
            'gambar'      => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
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
            'gambar.required'      => 'Gambar pengumuman wajib diunggah.',
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
