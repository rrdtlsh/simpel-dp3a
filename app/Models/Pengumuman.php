<?php
// FILE: app/Models/Pengumuman.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    // ✅ TAMBAHAN: Definisikan Konstanta Batas Karakter di sini
    public const JUDUL_MAX = 100;
    public const KONTEN_MAX = 2000;
    public const BADGE_LABEL_MAX = 30;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul',
        'konten',
        'gambar',
        'badge_label',
        'badge_color',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────────────
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scope: hanya yang aktif ───────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Accessor: URL publik gambar ───────────────────────────────────
    public function getGambarUrlAttribute(): string
    {
        return $this->gambar
            ? asset('storage/' . $this->gambar)
            : asset('images/placeholder.png');
    }
}
