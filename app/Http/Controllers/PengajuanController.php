<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\DokumenDiunggah;
use Illuminate\Support\Facades\Notification;
use App\Models\Bidang;
use App\Models\Pengajuan;
use App\Models\PengajuanFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PengajuanController extends Controller
{
    public function index()
    {
        $pengajuans = Pengajuan::with('bidang')
            ->forCurrentUserBidang()
            ->latest()
            ->paginate(10);

        return view('user.khp.permintaan', compact('pengajuans'));
    }

    public function adminContent()
    {
        $pengajuans = Pengajuan::with('bidang')->latest()->get();
        $bidangs = Bidang::whereIn('id', function ($query) {
            $query->select('bidang_id')
                ->from('users')
                ->whereNotNull('bidang_id');
        })->get();

        return view('admin.pengajuan.partials.permintaan', compact('pengajuans', 'bidangs'));
    }

    // 🧑‍💼 ADMIN: LIST PENGAJUAN + MODAL CREATE
    public function adminIndex()
    {
        $pengajuans = Pengajuan::with('bidang')
            ->latest()
            ->paginate(10);

        $bidangs = Bidang::all();

        return view('admin.pengajuan.index', compact('pengajuans', 'bidangs'));
    }

    // 🧑‍💼 ADMIN BUAT PENGAJUAN (DENGAN VALIDASI KETAT)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'unique:pengajuans,judul',
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/', // Anti karakter aneh
                function ($attr, $value, $fail) {
                    if (str_word_count((string) $value) > 20) {
                        $fail('Nama dokumen maksimal 20 kata.');
                    }
                },
            ],
            'deskripsi' => [
                'nullable', // Opsional sesuai desain form
                'string',
                'max:250',
                'regex:/^[^<>{}*^]*$/', // Anti XSS (Mencegah input script < >)
                function ($attr, $value, $fail) {
                    if ($value && str_word_count((string) $value) > 20) {
                        $fail('Deskripsi maksimal 20 kata.');
                    }
                },
            ],
            'bidang_id' => [
                'required',
                'exists:bidangs,id'
            ],
            'due_date' => [
                'required',
                'date',
                function ($attr, $value, $fail) {
                    if (Carbon::parse($value)->lessThanOrEqualTo(now())) {
                        $fail('Batas waktu (Deadline) harus di masa depan.');
                    }
                },
            ],
        ], [
            // Pesan Error Kustom Berbahasa Indonesia
            'judul.unique' => 'Nama dokumen ini sudah digunakan. Silakan gunakan nama lain.',
            'judul.required' => 'Nama dokumen wajib diisi.',
            'judul.min' => 'Nama dokumen terlalu pendek, minimal 5 karakter.',
            'judul.max' => 'Nama dokumen terlalu panjang, maksimal 50 karakter.',
            'judul.regex' => 'Format nama dokumen tidak valid. Hanya gunakan huruf, angka, spasi, dan simbol dasar (- / ( ) , .).',

            'deskripsi.max' => 'Deskripsi terlalu panjang, maksimal 250 karakter.',
            'deskripsi.regex' => 'Deskripsi mengandung karakter yang dilarang (< > { } * ^) demi keamanan.',

            'bidang_id.required' => 'Bidang tujuan wajib dipilih.',
            'bidang_id.exists' => 'Pilihan bidang tidak ditemukan dalam sistem.',

            'due_date.required' => 'Batas waktu (Deadline) wajib diisi.',
            'due_date.date' => 'Format tanggal dan waktu tidak valid.',
        ]);

        $dueDate = Carbon::parse($validated['due_date']);

        DB::transaction(function () use ($validated, $dueDate) {
            Pengajuan::create([
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'bidang_id' => $validated['bidang_id'],
                'due_date' => $dueDate,
                'status' => 'open',
                'created_by' => Auth::id(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Permintaan dokumen berhasil dibuat']);
        }

        return redirect()->back()->with('success', 'Permintaan dokumen berhasil dibuat');
    }

    // 👩‍💻 USER UPLOAD FILE (SUDAH DILENGKAPI PROTEKSI RACE CONDITION)
    public function upload(Request $request, $id)
    {
        // 1. Validasi File (Maksimal 5MB, format pdf/doc/xls)
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);

        $userId = Auth::id();

        // 2. Kunci unik Cache Lock (Pencegah user double-click)
        $lockKey = "upload_pengajuan_{$id}_user_{$userId}";
        $lock = Cache::lock($lockKey, 10); // Kunci selama 10 detik

        if ($lock->get()) {
            try {
                // 3. Cek riwayat upload user pada pengajuan ini
                $existingFile = PengajuanFile::where('pengajuan_id', $id)
                    ->where('user_id', $userId)
                    ->first();

                // Jika sudah pernah upload & statusnya belum ditolak (berarti pending/approved)
                if ($existingFile && $existingFile->status !== 'rejected') {
                    return back()->with('error', 'Anda sudah mengunggah dokumen untuk permintaan ini.');
                }

                // 4. Proses simpan file fisik
                $file = $request->file('file');
                $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $path = $file->storeAs('pengajuan_files', $filename, 'public');

                $pengajuan = Pengajuan::findOrFail($id); // Ambil data pengajuan untuk notif
                $isReupload = false;

                // 5. Simpan ke database
                if ($existingFile && $existingFile->status === 'rejected') {
                    // Jika file sebelumnya ditolak admin (Re-upload)
                    if (Storage::disk('public')->exists($existingFile->file_path)) {
                        Storage::disk('public')->delete($existingFile->file_path);
                    }

                    $existingFile->update([
                        'file_path' => $path,
                        'status' => 'pending',
                        'admin_notes' => null
                    ]);
                    $isReupload = true; // Tandai sebagai reupload
                } else {
                    // Jika ini adalah upload pertama kali
                    PengajuanFile::create([
                        'pengajuan_id' => $id,
                        'user_id' => $userId,
                        'file_path' => $path,
                        'status' => 'pending'
                    ]);
                }

                // === 6. KIRIM NOTIFIKASI KE ADMIN ===
                $admins = User::where('role', 'admin')->get(); // Ambil semua akun admin
                $namaPengirim = Auth::user()->bidang ? Auth::user()->bidang->nama : Auth::user()->name; // Gunakan nama bidang jika ada

                Notification::send($admins, new DokumenDiunggah($pengajuan, $namaPengirim, $isReupload));

                return back()->with('success', 'Dokumen berhasil diunggah.');
            } finally {
                // Wajib dilepas agar nanti bisa akses fitur lagi
                $lock->release();
            }
        } else {
            // Jika user menekan tombol upload berulang kali dengan sangat cepat
            return back()->with('error', 'Sistem sedang memproses unggahan Anda. Mohon tunggu sebentar.');
        }
    }

    // 🧑‍💼 ADMIN: PROSES REVIEW (TERIMA, TOLAK, ATAU SIMPAN CATATAN)
    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'admin_notes' => [
                'nullable',
                'string',
                function ($attr, $value, $fail) {
                    $plainText = strip_tags($value); // Hapus tag HTML dari WYSIWYG untuk dihitung
                    if (str_word_count($plainText) > 100) {
                        $fail('Catatan revisi maksimal 100 kata.');
                    }
                    if (strlen($plainText) > 1000) {
                        $fail('Catatan revisi terlalu panjang (maks 1000 karakter).');
                    }
                },
                'regex:/^[^<>{}^*^]*$/', // Mencegah script berbahaya
            ]
        ], [
            'admin_notes.regex' => 'Catatan mengandung karakter terlarang demi keamanan.',
        ]);

        // Temukan file berdasarkan ID Pengajuan (ambil yang paling baru)
        $file = PengajuanFile::where('pengajuan_id', $id)->latest()->first();

        if (!$file) {
            return response()->json(['message' => 'User belum mengunggah dokumen untuk pengajuan ini.'], 404);
        }

        // Update status dan catatan
        $file->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes']
        ]);

        return response()->json(['message' => 'Status dokumen berhasil diperbarui.']);
    }

    // 🧑‍💼 ADMIN UPDATE PENGAJUAN (DENGAN VALIDASI KETAT)
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'judul' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'unique:pengajuans,judul,' . $id,
                'regex:/^[a-zA-Z0-9\s\-\/\(\)\,\.]+$/', // Anti karakter aneh
                function ($attr, $value, $fail) {
                    if (str_word_count((string) $value) > 20) {
                        $fail('Nama dokumen maksimal 20 kata.');
                    }
                },
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:250',
                'regex:/^[^<>{}*^]*$/', // Anti XSS
                function ($attr, $value, $fail) {
                    if ($value && str_word_count((string) $value) > 20) {
                        $fail('Deskripsi maksimal 20 kata.');
                    }
                },
            ],
            'bidang_id' => [
                'required',
                'exists:bidangs,id'
            ],
            'due_date' => [
                'required',
                'date',
                function ($attr, $value, $fail) {
                    if (Carbon::parse($value)->lessThanOrEqualTo(now())) {
                        $fail('Batas waktu (Deadline) harus di masa depan.');
                    }
                },
            ],
        ], [
            // Pesan Error Kustom Berbahasa Indonesia
            'judul.unique' => 'Nama dokumen ini sudah digunakan. Silakan gunakan nama lain.',
            'judul.required' => 'Nama dokumen wajib diisi.',
            'judul.min' => 'Nama dokumen terlalu pendek, minimal 5 karakter.',
            'judul.max' => 'Nama dokumen terlalu panjang, maksimal 50 karakter.',
            'judul.regex' => 'Format nama dokumen tidak valid. Hanya gunakan huruf, angka, spasi, dan simbol dasar (- / ( ) , .).',

            'deskripsi.max' => 'Deskripsi terlalu panjang, maksimal 250 karakter.',
            'deskripsi.regex' => 'Deskripsi mengandung karakter yang dilarang (< > { } * ^) demi keamanan.',

            'bidang_id.required' => 'Bidang tujuan wajib dipilih.',
            'bidang_id.exists' => 'Pilihan bidang tidak ditemukan dalam sistem.',

            'due_date.required' => 'Batas waktu (Deadline) wajib diisi.',
            'due_date.date' => 'Format tanggal dan waktu tidak valid.',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);

        // Menggunakan array $validated agar lebih aman
        $pengajuan->update([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'bidang_id' => $validated['bidang_id'],
            'due_date' => Carbon::parse($validated['due_date']),
        ]);

        // Mengembalikan response JSON untuk ditangkap oleh fungsi fetch() di frontend
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Permintaan dokumen berhasil diperbarui']);
        }

        return redirect()->back()->with('success', 'Permintaan dokumen berhasil diperbarui');
    }

    // 🧑‍💼 ADMIN HAPUS PENGAJUAN
    public function destroy($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->delete();

        return response()->json(['message' => 'Permintaan berhasil dihapus']);
    }

    // 🧑‍💼 ADMIN: TAMPILAN HALAMAN VERIFIKASI
    public function verifikasiContent()
    {
        // Ambil data pengajuan beserta Bidang dan File terbarunya
        $pengajuans = Pengajuan::with(['bidang', 'files' => function ($query) {
            $query->latest(); // Ambil file yang paling baru di-upload
        }])->latest()->get();

        return view('admin.verifikasi.index', compact('pengajuans'));
    }

    // 🧑‍💼 ADMIN: TAMPILAN ARSIP DOKUMEN MASUK (YANG SUDAH DI-APPROVE)
    public function dokumenMasukContent()
    {
        // Ambil hanya pengajuan yang memiliki file dengan status 'approved'
        $pengajuans = Pengajuan::whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with(['bidang', 'files' => function ($q) {
            $q->where('status', 'approved')->latest();
        }])->latest()->get();

        // Ambil data untuk dropdown filter
        $bidangs = Bidang::all();
        $tahuns = $pengajuans->pluck('created_at')->map(function ($date) {
            return $date->format('Y');
        })->unique()->sortDesc();

        return view('admin.dokumen_masuk.index', compact('pengajuans', 'bidangs', 'tahuns'));
    }

    // 🧑‍💼 ADMIN: EXPORT PDF ARSIP
    public function exportPdf(Request $request)
    {
        // 1. Ambil data dengan relasi, terapkan filter jika ada (dikirim dari frontend)
        $query = Pengajuan::whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with(['bidang', 'files' => function ($q) {
            $q->where('status', 'approved')->latest();
        }]);

        if ($request->bidang) {
            $query->whereHas('bidang', function ($q) use ($request) {
                $q->where('nama', $request->bidang);
            });
        }
        if ($request->tahun) {
            $query->whereYear('created_at', $request->tahun);
        }

        $pengajuans = $query->latest()->get();

        // 2. Generate PDF dari View
        $pdf = Pdf::loadView('admin.dokumen_masuk.pdf', compact('pengajuans'));

        // 3. Download filenya
        return $pdf->download('Arsip_Dokumen_DP3A_' . time() . '.pdf');
    }

    // 🧑‍💼 ADMIN: EXPORT EXCEL (CSV STREAM SERVER-SIDE)
    public function exportExcel(Request $request)
    {
        $query = Pengajuan::whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with(['bidang', 'files' => function ($q) {
            $q->where('status', 'approved')->latest();
        }]);

        if ($request->bidang) {
            $query->whereHas('bidang', function ($q) use ($request) {
                $q->where('nama', $request->bidang);
            });
        }
        if ($request->tahun) {
            $query->whereYear('created_at', $request->tahun);
        }

        $pengajuans = $query->latest()->get();

        $fileName = 'Arsip_Dokumen_DP3A_' . time() . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Format CSV agar rapi di MS Excel
        $callback = function () use ($pengajuans) {
            $file = fopen('php://output', 'w');
            // Menambahkan BOM agar Excel membaca karakter dengan benar
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, ['No', 'Nama Dokumen', 'Bidang Tujuan', 'Tahun', 'Tanggal Diterima']);

            foreach ($pengajuans as $index => $row) {
                $fileData = $row->files->first();
                fputcsv($file, [
                    $index + 1,
                    $row->judul,
                    $row->bidang->nama ?? '-',
                    $row->created_at->format('Y'),
                    $fileData ? $fileData->updated_at->format('d M Y H:i') : '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
