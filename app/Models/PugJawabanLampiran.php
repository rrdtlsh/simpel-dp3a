<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugJawabanLampiran extends Model
{
    protected $table    = 'pug_jawaban_lampiran';
    protected $fillable = ['jawaban_id', 'nama_file', 'path_file', 'mime_type', 'ukuran', 'diupload_oleh'];
    public function jawaban()
    {
        return $this->belongsTo(PugJawaban::class, 'jawaban_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'diupload_oleh');
    }
}
