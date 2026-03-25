<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Pengajuan extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'bidang_id',
        'due_date',
        'status',
        'created_by'
    ];

    // Relasi ke Bidang
    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    // relasi ke file upload user
    public function files()
    {
        return $this->hasMany(PengajuanFile::class);
    }

    // relasi admin pembuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope untuk filter otomatis
    public function scopeForCurrentUserBidang($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        // Kalau admin → lihat semua
        if ($user->role === 'admin') {
            return $query;
        }

        // Kalau user → filter berdasarkan bidang
        return $query->where('bidang_id', $user->bidang_id);
    }
}
