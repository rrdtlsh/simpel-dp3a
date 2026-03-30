<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugPertanyaan extends Model
{
    protected $table    = 'pug_pertanyaan';
    protected $fillable = ['indikator_id', 'kode', 'pertanyaan', 'skor_maksimal', 'pilihan_jawaban', 'petunjuk', 'urutan', 'aktif'];
    protected $casts    = ['pilihan_jawaban' => 'array'];
    public function indikator()
    {
        return $this->belongsTo(PugIndikator::class, 'indikator_id');
    }
    public function jawaban()
    {
        return $this->hasMany(PugJawaban::class, 'pertanyaan_id');
    }
}
