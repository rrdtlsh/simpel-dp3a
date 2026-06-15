<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'judul' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/',
                // ✅ VALIDASI CUSTOM: Mengecek apakah nama dokumen sudah ada
                function ($attr, $value, $fail) {
                    $bidangId = request('bidang_id');
                    
                    if ($bidangId === 'all') {
                        // Jika dikirim ke Semua Bidang, cek apakah nama dokumen ini sudah ada sama sekali di database
                        if (\App\Models\Pengajuan::where('judul', $value)->exists()) {
                            $fail('Nama dokumen ini sudah ada di database. Silakan gunakan nama lain agar tidak duplikat.');
                        }
                    } else {
                        // Jika dikirim ke 1 Bidang spesifik, cek apakah bidang tersebut sudah punya dokumen dengan nama ini
                        if (\App\Models\Pengajuan::where('judul', $value)->where('bidang_id', $bidangId)->exists()) {
                            $fail('Nama dokumen ini sudah pernah dikirimkan pada bidang yang Anda pilih.');
                        }
                    }
                },
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

            'bidang_id' => [
                'required',
                // ✅ VALIDASI CUSTOM: Mengizinkan value "all" ATAU ID bidang yang valid
                function ($attr, $value, $fail) {
                    if ($value !== 'all' && !\App\Models\Bidang::where('id', $value)->exists()) {
                        $fail('Pilihan bidang tidak ditemukan dalam sistem.');
                    }
                },
            ],

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
            // 'judul.unique' dihapus karena sudah di-handle oleh validasi custom di atas
            'judul.required'     => 'Nama dokumen wajib diisi.',
            'judul.min'          => 'Nama dokumen terlalu pendek, minimal 5 karakter.',
            'judul.max'          => 'Nama dokumen terlalu panjang, maksimal 50 karakter.',
            'judul.regex'        => 'Format nama dokumen tidak valid. Hanya gunakan huruf, angka, spasi, dan simbol dasar.',
            
            'deskripsi.max'      => 'Deskripsi terlalu panjang, maksimal 250 karakter.',
            'deskripsi.regex'    => 'Deskripsi mengandung karakter yang dilarang (< > { } * ^) demi keamanan.',
            
            'bidang_id.required' => 'Bidang tujuan wajib dipilih.',
            
            'due_date.required'  => 'Batas waktu (Deadline) wajib diisi.',
            'due_date.date'      => 'Format tanggal dan waktu tidak valid.',
        ];
    }
}