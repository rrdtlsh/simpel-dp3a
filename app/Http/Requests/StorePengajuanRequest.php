<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ubah menjadi true agar semua user (yang punya akses ke route) bisa menggunakan ini
    }

    public function rules()
    {
        return [
            'judul' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'unique:pengajuans,judul',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/',
                function ($attr, $value, $fail) {
                    if (str_word_count((string) $value) > 20) $fail('Nama dokumen maksimal 20 kata.');
                },
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:250',
                'regex:/^[^<>{}*^]*$/',
                function ($attr, $value, $fail) {
                    if ($value && str_word_count((string) $value) > 20) $fail('Deskripsi maksimal 20 kata.');
                },
            ],
            'bidang_id' => ['required', 'exists:bidangs,id'],
            'tahun'     => ['required', 'digits:4'],
            'due_date'  => [
                'required',
                'date',
                function ($attr, $value, $fail) {
                    if (Carbon::parse($value)->lessThanOrEqualTo(now())) $fail('Batas waktu (Deadline) harus di masa depan.');
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'judul.unique'       => 'Nama dokumen ini sudah digunakan. Silakan gunakan nama lain.',
            'judul.required'     => 'Nama dokumen wajib diisi.',
            'judul.min'          => 'Nama dokumen terlalu pendek, minimal 5 karakter.',
            'judul.max'          => 'Nama dokumen terlalu panjang, maksimal 50 karakter.',
            'judul.regex'        => 'Format nama dokumen tidak valid. Hanya gunakan huruf, angka, spasi, dan simbol dasar.',
            'deskripsi.max'      => 'Deskripsi terlalu panjang, maksimal 250 karakter.',
            'deskripsi.regex'    => 'Deskripsi mengandung karakter yang dilarang (< > { } * ^) demi keamanan.',
            'bidang_id.required' => 'Bidang tujuan wajib dipilih.',
            'bidang_id.exists'   => 'Pilihan bidang tidak ditemukan dalam sistem.',
            'due_date.required'  => 'Batas waktu (Deadline) wajib diisi.',
            'due_date.date'      => 'Format tanggal dan waktu tidak valid.',
        ];
    }
}
