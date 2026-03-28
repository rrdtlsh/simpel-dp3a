<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PermintaanBaruNotification extends Notification
{
    use Queueable;

    protected $pengajuan;

    public function __construct($pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'pengajuan_id' => $this->pengajuan->id,
            'judul'        => $this->pengajuan->judul,
            'pengirim'     => 'Admin DP3A',
            'pesan'        => 'meminta dokumen baru:',
            'icon'         => 'fa-file-circle-plus',
            'color'        => '#eef2ff',   // Biru muda
            'text_color'   => '#4f46e5',   // Biru tua
            'page'         => 'permintaan', // Supaya saat di-klik, masuk ke menu permintaan
        ];
    }
}
