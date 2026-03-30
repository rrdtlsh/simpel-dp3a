<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugIndikator extends Model
{
    protected $table    = 'pug_indikator';
    protected $fillable = ['komponen_id', 'kode', 'nama', 'urutan', 'aktif'];
    public function komponen()
    {
        return $this->belongsTo(PugKomponen::class, 'komponen_id');
    }
    public function pertanyaan()
    {
        return $this->hasMany(PugPertanyaan::class, 'indikator_id');
    }
}
