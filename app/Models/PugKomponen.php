<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugKomponen extends Model
{
    protected $table    = 'pug_komponen';
    protected $fillable = ['kode', 'nama', 'level', 'urutan', 'aktif'];
    public function indikator()
    {
        return $this->hasMany(PugIndikator::class, 'komponen_id');
    }
}
