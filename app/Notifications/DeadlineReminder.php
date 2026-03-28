<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineReminder extends Notification
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
            'pengirim'     => 'Sistem',
            'pesan'        => 'PENGINGAT: Besok adalah tenggat waktu terakhir untuk',
            'icon'         => 'fa-clock',
            'color'        => '#fef3c7',    // Background kuning lembut
            'text_color'   => '#d97706',    // Teks oranye gelap
            'page'         => 'permintaan', // Arahkan user ke halaman permintaan
        ];
    }
}
