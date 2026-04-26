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
        'email',
        'role',
        'password',
        'bidang_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
