<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\DokumenDiunggah;
use App\Notifications\DokumenDiperiksa;
use App\Notifications\PermintaanBaruNotification;
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
use Illuminate\Support\Facades\Validator;

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
        $pengajuans = Pengajuan::with([
            'bidang',
            'files' => fn($q) => $q->latest(), // ← INI yang hilang sebelumnya
        ])->latest()->get();

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
            'tahun' => [
                'required',
                'digits:4',
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

        $pengajuan = DB::transaction(function () use ($validated, $dueDate) {
            return Pengajuan::create([
                'judul'      => $validated['judul'],
                'deskripsi'  => $validated['deskripsi'] ?? null,
                'bidang_id'  => $validated['bidang_id'],
                'tahun'      => $validated['tahun'],
                'due_date'   => $dueDate,
                'status'     => 'open',
                'created_by' => Auth::id(),
            ]);
        });

        $bidang = Bidang::with('users')->find($request->bidang_id);
        if ($bidang && $bidang->users->isNotEmpty()) {
            Notification::send($bidang->users, new PermintaanBaruNotification($pengajuan));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Permintaan dokumen berhasil dibuat']);
        }

        return redirect()->back()->with('success', 'Permintaan dokumen berhasil dibuat');
    }

    // 👩‍💻 USER: FUNGSI UPLOAD MULTI-FILE (CLAUDE AI + KOREKSI IDE)
    public function upload(Request $request, $id)
    {
        // 1. VALIDASI SERVER-SIDE (Tanpa backslash agar IDE tidak protes)
        $validator = Validator::make($request->all(), [
            'files'          => ['nullable', 'array', 'max:5'],
            'files.*'        => ['file', 'max:8192', 'mimes:pdf,doc,docx,xls,xlsx'],
            'retained_files' => ['nullable', 'array'],
            'user_notes'     => ['nullable', 'string', 'max:250'],
        ], [
            'files.required'   => 'Tidak ada file yang diunggah.',
            'files.max'      => 'Maksimal 5 file baru dalam satu kali unggah.',
            'files.*.max'    => 'Setiap file maksimal berukuran 8 MB.',
            'files.*.mimes'  => 'Ekstensi file tidak diizinkan. Gunakan: PDF, DOC, DOCX, XLS, atau XLSX.',
            'user_notes.max' => 'Pesan/Catatan terlalu panjang (Maksimal 250 karakter).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $newFilesCount = $request->hasFile('files') ? count($request->file('files')) : 0;
        $retainedCount = $request->has('retained_files') ? count($request->retained_files) : 0;
        $totalFiles    = $newFilesCount + $retainedCount;

        if ($totalFiles === 0) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => ['files' => ['Tidak ada file yang disimpan. Minimal 1 file.']]], 422);
        }
        if ($totalFiles > 5) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => ['files' => ['Total akumulasi file (lama + baru) maksimal 5 file.']]], 422);
        }

        $user = $request->user();

        // 2. AMBIL DATA PENGAJUAN
        $pengajuan = Pengajuan::where('id', $id)->where('bidang_id', $user->bidang_id)->firstOrFail();
        $pengajuanFile = PengajuanFile::where('pengajuan_id', $pengajuan->id)->first();

        // 3. PROTEKSI RE-UPLOAD
        if ($pengajuanFile && !in_array($pengajuanFile->status, ['rejected', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dapat diubah karena sudah diverifikasi (Selesai).',
            ], 403);
        }

        // 4. PROSES FILE LAMA (HAPUS YANG TIDAK DIPERTAHANKAN OLEH USER)
        $existingFiles = $pengajuanFile ? ($pengajuanFile->files ?? []) : [];
        $retainedPaths = $request->input('retained_files', []);
        $finalExistingFiles = [];

        foreach ($existingFiles as $oldFile) {
            // Jika path lama ada di daftar yang dipertahankan User, simpan ke array final
            if (isset($oldFile['path']) && in_array($oldFile['path'], $retainedPaths)) {
                $finalExistingFiles[] = $oldFile;
            } else {
                // Jika tidak ada di daftar, berarti user menghapusnya di Modal UI. Hapus fisiknya!
                if (isset($oldFile['path']) && Storage::disk('public')->exists($oldFile['path'])) {
                    Storage::disk('public')->delete($oldFile['path']);
                }
            }
        }

        // 5. SIMPAN FILE BARU KE STORAGE
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                $safeName     = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                $uniqueName   = time() . '_' . uniqid() . '_' . $safeName;

                $storedPath = $file->storeAs('pengajuan_files', $uniqueName, 'public');
                if ($storedPath) {
                    $uploadedFiles[] = [
                        'path'          => $storedPath,
                        'original_name' => $originalName,
                        'size'          => $file->getSize(),
                        'mime'          => $file->getMimeType(),
                        'uploaded_at'   => now()->toDateTimeString(),
                    ];
                }
            }
        }

        // 6. GABUNGKAN DATA & SIMPAN KE TABEL
        $finalFilesToSave = array_merge($finalExistingFiles, $uploadedFiles);

        PengajuanFile::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'user_id' => $user->id,
                'files' => $finalFilesToSave,
                'status' => 'pending', // Reset ke pending setiap kali ada update/hapus file
                'admin_notes' => null,
                'user_notes'  => $request->input('user_notes'),
            ]
        );

        // 7. KIRIM NOTIFIKASI KE ADMIN
        $admins = User::where('role', 'admin')->get();
        if ($admins->count() > 0) {
            $namaPengirim = $user->bidang ? $user->bidang->nama : $user->name;
            $isReupload = $pengajuanFile ? true : false;
            Notification::send($admins, new DokumenDiunggah($pengajuan, $namaPengirim, $isReupload));
        }

        return response()->json([
            'success' => true,
            'message' => count($finalFilesToSave) . ' file berhasil disimpan.',
            'total_files' => count($finalFilesToSave),
            'files' => array_map(fn($f) => ['name' => $f['original_name'], 'size' => $f['size'] ?? 0], $finalFilesToSave),
        ], 200);
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
                    // Bersihkan HTML tag dan spasi kosong untuk menghitung panjang asli teks
                    $plainText = trim(strip_tags(html_entity_decode($value)));
                    if (mb_strlen($plainText) > 500) {
                        $fail('Catatan revisi terlalu panjang (maksimal 500 karakter teks).');
                    }
                }
            ]
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

        if ($file->user) {
            Notification::send($file->user, new DokumenDiperiksa($file));
        }

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

    // 🧑‍💼 ADMIN HAPUS PENGAJUAN (DENGAN PEMBERSIHAN STORAGE)
    public function destroy($id)
    {
        $pengajuan = Pengajuan::with('files')->findOrFail($id);

        foreach ($pengajuan->files as $pengajuanFile) {
            if (!empty($pengajuanFile->files)) {
                foreach ($pengajuanFile->files as $fileData) {
                    if (isset($fileData['path']) && Storage::disk('public')->exists($fileData['path'])) {
                        Storage::disk('public')->delete($fileData['path']);
                    }
                }
            }
        }
        $pengajuan->delete();

        return response()->json(['message' => 'Permintaan dan file lampiran berhasil dihapus']);
    }

    // 🧑‍💼 ADMIN: TAMPILAN HALAMAN VERIFIKASI
    public function verifikasiContent()
    {
        $pengajuans = Pengajuan::with([
            'bidang',
            'files' => fn($q) => $q->latest(),
        ])->latest()->get();

        return view('admin.verifikasi.index', compact('pengajuans'));
    }

    // 🧑‍💼 ADMIN: TAMPILAN ARSIP DOKUMEN MASUK (YANG SUDAH DI-APPROVE)
    public function dokumenMasukContent()
    {
        $pengajuans = Pengajuan::whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with([
            'bidang',
            'files' => fn($q) => $q->where('status', 'approved')->latest(),
        ])->latest()->get();

        $bidangs = Bidang::all();
        $tahuns = $pengajuans->pluck('tahun')
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

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
                    $row->tahun ?? '-',
                    $fileData ? $fileData->updated_at->format('d M Y H:i') : '-'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // 👩‍💻 USER: HALAMAN DAFTAR PERMINTAAN DOKUMEN
    public function userPermintaan()
    {
        $pengajuans = Pengajuan::forCurrentUserBidang()
            ->with(['files' => fn($q) => $q->latest()])
            ->latest()
            ->get();

        return view('user.permintaan.index', compact('pengajuans'));
    }

    // 👩‍💻 USER: HALAMAN ARSIP
    public function userArsip()
    {
        $pengajuans = Pengajuan::forCurrentUserBidang()
            ->whereHas('files', function ($q) {
                $q->where('status', 'approved');
            })
            ->with(['files' => function ($q) {
                $q->where('status', 'approved')->latest();
            }])
            ->latest()
            ->get();

        $tahuns = $pengajuans->pluck('tahun')->filter()->unique()->sortDesc()->values();

        return view('user.arsip.index', compact('pengajuans', 'tahuns'));
    }

    // 👩‍💻 USER: EXPORT PDF ARSIP
    public function exportPdfUser(Request $request)
    {
        $query = Pengajuan::forCurrentUserBidang()->whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with(['bidang', 'files' => function ($q) {
            $q->where('status', 'approved')->latest();
        }]);

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $pengajuans = $query->latest()->get();
        // Menggunakan view yang sama dengan admin agar hemat memori (tampilannya sama saja)
        $pdf = Pdf::loadView('admin.dokumen_masuk.pdf', compact('pengajuans'));
        return $pdf->download('Arsip_Bidang_' . Auth::user()->bidang->nama . '_' . time() . '.pdf');
    }

    // 👩‍💻 USER: EXPORT EXCEL ARSIP
    public function exportExcelUser(Request $request)
    {
        $query = Pengajuan::forCurrentUserBidang()->whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with(['bidang', 'files' => function ($q) {
            $q->where('status', 'approved')->latest();
        }]);

        if ($request->tahun) {
            $query->where('tahun', $request->tahun);
        }

        $pengajuans = $query->latest()->get();
        $fileName = 'Arsip_Bidang_' . Auth::user()->bidang->nama . '_' . time() . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($pengajuans) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, ['No', 'Nama Dokumen', 'Bidang Tujuan', 'Tahun', 'Tanggal Diterima']);
            foreach ($pengajuans as $index => $row) {
                $fileData = $row->files->first();
                fputcsv($file, [
                    $index + 1,
                    $row->judul,
                    $row->bidang->nama ?? '-',
                    $row->tahun ?? '-',
                    $fileData ? $fileData->updated_at->format('d M Y H:i') : '-'
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // [TUGAS 2B] METHOD BARU — tambahkan ke PengajuanController.php
    // ─────────────────────────────────────────────────────────────────────────────

    public function userDashboard()
    {
        // Ambil semua pengajuan untuk bidang user yang login
        $pengajuans = Pengajuan::forCurrentUserBidang()
            ->with(['files' => fn($q) => $q->latest()])
            ->latest()
            ->get();

        // Hitung statistik berdasarkan status FILE (bukan status pengajuan)
        $stats = [
            'total'    => $pengajuans->count(),

            // 'open' = pengajuan yang belum ada file sama sekali (belum diupload)
            'open'     => $pengajuans->filter(fn($p) => $p->files->isEmpty())->count(),

            // 'pending' = sudah upload, menunggu review admin
            'pending'  => $pengajuans->filter(
                fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'pending'
            )->count(),

            // 'rejected' = ditolak admin, perlu revisi
            'rejected' => $pengajuans->filter(
                fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'rejected'
            )->count(),

            // 'approved' = diterima admin
            'approved' => $pengajuans->filter(
                fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'approved'
            )->count(),
        ];

        return view('user.dashboarduser', compact('stats'));
    }
}
