<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PugAuditLog extends Model
{
    protected $table    = 'pug_audit_log';
    protected $fillable = ['jawaban_id', 'user_id', 'aksi', 'sebelum', 'sesudah', 'ip_address'];
    protected $casts    = ['sebelum' => 'array', 'sesudah' => 'array'];
    public function jawaban()
    {
        return $this->belongsTo(PugJawaban::class, 'jawaban_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
