<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenDiunggah extends Notification
{
    use Queueable;

    protected $pengajuan;
    protected $namaUser;
    protected $isReupload;

    /**
     * Create a new notification instance.
     */
    public function __construct($pengajuan, $namaUser, $isReupload = false)
    {
        $this->pengajuan = $pengajuan;
        $this->namaUser = $namaUser;
        $this->isReupload = $isReupload;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Menyimpan notifikasi ke dalam tabel database
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'pengajuan_id' => $this->pengajuan->id,
            'judul' => $this->pengajuan->judul,
            'pengirim' => $this->namaUser,
            'tipe' => $this->isReupload ? 'reupload' : 'upload',
            'pesan' => $this->isReupload
                ? 'memperbarui dokumen yang direvisi pada'
                : 'mengunggah dokumen baru pada',
            'icon' => $this->isReupload ? 'fa-file-pen' : 'fa-file-arrow-up',
            'color' => $this->isReupload ? '#fff3cd' : '#eaf6fc',
            'text_color' => $this->isReupload ? '#856404' : '#067fb2',

            'page' => 'verifikasi',
        ];
    }
}
