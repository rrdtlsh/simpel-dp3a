<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nip',
        'role',
        'password',
        'bidang_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke bidang
    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    // Scope helper: filter query berdasarkan bidang user login
    public function scopeForCurrentUserBidang($query)
    {
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        // Admin bisa lihat semua
        if ($user->role === 'admin') {
            return $query;
        }

        // User biasa: filter berdasarkan bidang_id-nya
        return $query->where('bidang_id', $user->bidang_id);
    }

    /**
     * Cast attributes
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
