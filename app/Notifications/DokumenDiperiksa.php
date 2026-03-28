<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenDiperiksa extends Notification
{
    use Queueable;

    protected $pengajuanFile;

    /**
     * Menerima data pengajuan_files yang direview
     */
    public function __construct($pengajuanFile)
    {
        $this->pengajuanFile = $pengajuanFile;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $status = $this->pengajuanFile->status;
        $isApproved = ($status === 'approved');

        // Ambil judul dari relasi pengajuan
        $judulDokumen = $this->pengajuanFile->pengajuan->judul ?? 'Dokumen';

        return [
            'pengajuan_id' => $this->pengajuanFile->pengajuan_id,
            'judul' => $judulDokumen,
            'pengirim' => 'Admin DP3A',
            'pesan' => $isApproved ? 'telah MENERIMA dokumen' : 'telah MENOLAK dokumen (Perlu Revisi)',
            'icon' => $isApproved ? 'fa-check' : 'fa-xmark',
            'color' => $isApproved ? '#dcfce7' : '#fee2e2',
            'text_color' => $isApproved ? '#16a34a' : '#dc2626',

            // Tambahkan 'page' agar JS tahu harus mengarahkan user ke menu mana
            'page' => $isApproved ? 'arsip' : 'permintaan',
        ];
    }
}
