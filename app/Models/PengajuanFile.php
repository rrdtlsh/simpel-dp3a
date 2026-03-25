<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanFile extends Model
{
    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'file_path',
        'status',
        'admin_notes'
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store(Request $request)
    {
        Pengajuan::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'bidang_id' => $request->bidang_id,
            'due_date' => $request->due_date,
            'created_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Pengajuan dibuat');
    }
}
