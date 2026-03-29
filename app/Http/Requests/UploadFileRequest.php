<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadFileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'files'          => ['nullable', 'array', 'max:5'],
            'files.*'        => ['file', 'max:8192', 'mimes:pdf,doc,docx,xls,xlsx'],
            'retained_files' => ['nullable', 'array'],
            'user_notes'     => ['nullable', 'string', 'max:250'],
        ];
    }

    public function messages()
    {
        return [
            'files.required'   => 'Tidak ada file yang diunggah.',
            'files.max'        => 'Maksimal 5 file baru dalam satu kali unggah.',
            'files.*.max'      => 'Setiap file maksimal berukuran 8 MB.',
            'files.*.mimes'    => 'Ekstensi file tidak diizinkan. Gunakan: PDF, DOC, DOCX, XLS, atau XLSX.',
            'user_notes.max'   => 'Pesan/Catatan terlalu panjang (Maksimal 250 karakter).',
        ];
    }

    // KUSTOMISASI: Karena kita menggunakan AJAX/Fetch API di frontend, 
    // jika validasi gagal, kembalikan response JSON persis seperti sebelumnya
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors'  => $validator->errors()->toArray(),
        ], 422));
    }
}
