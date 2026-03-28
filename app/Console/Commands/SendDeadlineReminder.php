<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pengajuan;
use App\Notifications\DeadlineReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class SendDeadlineReminder extends Command
{
    // Ini adalah nama perintah yang akan kita jalankan di terminal nanti
    protected $signature = 'reminder:deadline';

    protected $description = 'Kirim notifikasi H-1 sebelum deadline dokumen';

    public function handle()
    {
        // 1. Cari tanggal besok
        $besok = Carbon::tomorrow()->toDateString();

        // 2. Cari dokumen yang deadlinenya besok DAN belum diterima (approved)
        $pengajuans = Pengajuan::whereDate('due_date', $besok)
            ->whereDoesntHave('files', function ($q) {
                $q->where('status', 'approved');
            })->get();

        // 3. Kirim notifikasi ke masing-masing user di bidang tersebut
        foreach ($pengajuans as $pengajuan) {
            // Asumsi relasi Bidang -> Users sudah dibuat (HasMany)
            $users = $pengajuan->bidang->users ?? [];

            if (count($users) > 0) {
                Notification::send($users, new DeadlineReminder($pengajuan));
            }
        }

        // 4. Pesan sukses di terminal
        $this->info('Sukses! Reminder deadline telah dikirim untuk ' . $pengajuans->count() . ' dokumen.');
    }
}
