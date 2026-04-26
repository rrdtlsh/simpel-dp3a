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
        'tahun',
        'due_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function files()
    {
        return $this->hasMany(PengajuanFile::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCurrentUserBidang($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where('bidang_id', $user->bidang_id);
    }
}
