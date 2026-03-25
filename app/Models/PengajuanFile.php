<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'file_path',
        'status',
        'admin_notes'
    ];

    protected $casts = [
        'files' => 'array',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
