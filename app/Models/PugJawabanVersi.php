<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugJawabanVersi extends Model
{
    protected $table    = 'pug_jawaban_versi';
    protected $fillable = ['jawaban_id', 'user_id', 'versi', 'jawaban_kode', 'jawaban_label', 'catatan', 'skor', 'status'];
    public function jawaban()
    {
        return $this->belongsTo(PugJawaban::class, 'jawaban_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
