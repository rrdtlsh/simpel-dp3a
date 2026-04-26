<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugJawaban extends Model
{
    protected $table    = 'pug_jawaban';
    protected $fillable = [
        'pertanyaan_id',
        'tahun',
        'jawaban_kode',
        'jawaban_label',
        'catatan',
        'skor',
        'status',
        'diisi_oleh',
        'diverifikasi_oleh',
        'diverifikasi_at',
        'catatan_admin'
    ];
    protected $casts    = ['diverifikasi_at' => 'datetime'];
    public function pertanyaan()
    {
        return $this->belongsTo(PugPertanyaan::class, 'pertanyaan_id');
    }
    public function lampiran()
    {
        return $this->hasMany(PugJawabanLampiran::class, 'jawaban_id');
    }
    public function diisiOleh()
    {
        return $this->belongsTo(User::class, 'diisi_oleh');
    }
    public function diverifikasiOleh()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
