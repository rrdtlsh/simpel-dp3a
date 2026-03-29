<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidang;
use App\Models\Pengajuan;
use App\Models\PengajuanFile;
use App\Http\Requests\StorePengajuanRequest;
use App\Http\Requests\UpdatePengajuanRequest;
use App\Http\Requests\UploadFileRequest;
use App\Notifications\DokumenDiunggah;
use App\Notifications\DokumenDiperiksa;
use App\Notifications\PermintaanBaruNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class PengajuanController extends Controller
{
    /* ══════════════════════════════════════════════════════════════════════════
       BAGIAN 1: FUNGSI ADMIN - MANAJEMEN PERMINTAAN & VERIFIKASI
       ══════════════════════════════════════════════════════════════════════════ */

    public function adminIndex()
    {
        $pengajuans = Pengajuan::with('bidang')->latest()->paginate(10);
        $bidangs = Bidang::all();

        return view('admin.pengajuan.index', compact('pengajuans', 'bidangs'));
    }

    public function adminContent()
    {
        $pengajuans = Pengajuan::with([
            'bidang',
            'files' => fn($q) => $q->latest(),
        ])->latest()->get();

        $bidangs = Bidang::whereIn('id', function ($query) {
            $query->select('bidang_id')
                ->from('users')
                ->whereNotNull('bidang_id');
        })->get();

        return view('admin.pengajuan.partials.permintaan', compact('pengajuans', 'bidangs'));
    }

    public function store(StorePengajuanRequest $request)
    {
        $validated = $request->validated();
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

    public function update(UpdatePengajuanRequest $request, $id)
    {
        $validated = $request->validated();

        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->update([
            'judul'     => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'bidang_id' => $validated['bidang_id'],
            'due_date'  => Carbon::parse($validated['due_date']),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Permintaan dokumen berhasil diperbarui']);
        }
        return redirect()->back()->with('success', 'Permintaan dokumen berhasil diperbarui');
    }

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

    public function verifikasiContent()
    {
        $pengajuans = Pengajuan::with([
            'bidang',
            'files' => fn($q) => $q->latest(),
        ])->latest()->get();

        return view('admin.verifikasi.index', compact('pengajuans'));
    }

    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'status'      => 'required|in:pending,approved,rejected',
            'admin_notes' => [
                'nullable',
                'string',
                function ($attr, $value, $fail) {
                    $plainText = trim(strip_tags(html_entity_decode($value)));
                    if (mb_strlen($plainText) > 500) $fail('Catatan revisi terlalu panjang (maksimal 500 karakter teks).');
                }
            ]
        ]);

        $file = PengajuanFile::where('pengajuan_id', $id)->latest()->first();

        if (!$file) {
            return response()->json(['message' => 'User belum mengunggah dokumen untuk pengajuan ini.'], 404);
        }

        $file->update([
            'status'      => $validated['status'],
            'admin_notes' => $validated['admin_notes']
        ]);

        if ($file->user) {
            Notification::send($file->user, new DokumenDiperiksa($file));
        }

        return response()->json(['message' => 'Status dokumen berhasil diperbarui.']);
    }

    public function dokumenMasukContent()
    {
        $pengajuans = Pengajuan::whereHas('files', function ($q) {
            $q->where('status', 'approved');
        })->with([
            'bidang',
            'files' => fn($q) => $q->where('status', 'approved')->latest(),
        ])->latest()->get();

        $bidangs = Bidang::all();
        $tahuns  = $pengajuans->pluck('tahun')->filter()->unique()->sortDesc()->values();

        return view('admin.dokumen_masuk.index', compact('pengajuans', 'bidangs', 'tahuns'));
    }


    /* ══════════════════════════════════════════════════════════════════════════
       BAGIAN 2: FUNGSI ADMIN - EXPORT DATA
       ══════════════════════════════════════════════════════════════════════════ */

    public function exportPdf(Request $request)
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
        $pdf = Pdf::loadView('admin.dokumen_masuk.pdf', compact('pengajuans'));

        return $pdf->download('Arsip_Dokumen_DP3A_' . time() . '.pdf');
    }

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

        $callback = function () use ($pengajuans) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // BOM for Excel UTF-8
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


    /* ══════════════════════════════════════════════════════════════════════════
       BAGIAN 3: FUNGSI USER - DASHBOARD, PERMINTAAN & UPLOAD
       ══════════════════════════════════════════════════════════════════════════ */

    public function index()
    {
        $pengajuans = Pengajuan::with('bidang')
            ->forCurrentUserBidang()
            ->latest()
            ->paginate(10);

        return view('user.khp.permintaan', compact('pengajuans'));
    }

    public function userDashboard()
    {
        $pengajuans = Pengajuan::forCurrentUserBidang()
            ->with(['files' => fn($q) => $q->latest()])
            ->latest()
            ->get();

        $stats = [
            'total'    => $pengajuans->count(),
            'open'     => $pengajuans->filter(fn($p) => $p->files->isEmpty())->count(),
            'pending'  => $pengajuans->filter(fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'pending')->count(),
            'rejected' => $pengajuans->filter(fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'rejected')->count(),
            'approved' => $pengajuans->filter(fn($p) => $p->files->isNotEmpty() && $p->files->first()->status === 'approved')->count(),
        ];

        return view('user.dashboarduser', compact('stats'));
    }

    public function userPermintaan()
    {
        $pengajuans = Pengajuan::forCurrentUserBidang()
            ->with(['files' => fn($q) => $q->latest()])
            ->latest()
            ->get();

        return view('user.permintaan.index', compact('pengajuans'));
    }

    public function upload(UploadFileRequest $request, $id)
    {

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
        $pengajuan = Pengajuan::where('id', $id)->where('bidang_id', $user->bidang_id)->firstOrFail();
        $pengajuanFile = PengajuanFile::where('pengajuan_id', $pengajuan->id)->first();


        if ($pengajuanFile && !in_array($pengajuanFile->status, ['rejected', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dapat diubah karena sudah diverifikasi (Selesai).',
            ], 403);
        }

        $existingFiles = $pengajuanFile ? ($pengajuanFile->files ?? []) : [];
        $retainedPaths = $request->input('retained_files', []);
        $finalExistingFiles = [];

        foreach ($existingFiles as $oldFile) {
            if (isset($oldFile['path']) && in_array($oldFile['path'], $retainedPaths)) {
                $finalExistingFiles[] = $oldFile;
            } else {
                if (isset($oldFile['path']) && Storage::disk('public')->exists($oldFile['path'])) {
                    Storage::disk('public')->delete($oldFile['path']);
                }
            }
        }

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

        $finalFilesToSave = array_merge($finalExistingFiles, $uploadedFiles);

        PengajuanFile::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'user_id'     => $user->id,
                'files'       => $finalFilesToSave,
                'status'      => 'pending',
                'admin_notes' => null,
                'user_notes'  => $request->input('user_notes'),
            ]
        );

        $admins = User::where('role', 'admin')->get();
        if ($admins->count() > 0) {
            $namaPengirim = $user->bidang ? $user->bidang->nama : $user->name;
            $isReupload = $pengajuanFile ? true : false;
            Notification::send($admins, new DokumenDiunggah($pengajuan, $namaPengirim, $isReupload));
        }

        return response()->json([
            'success'     => true,
            'message'     => count($finalFilesToSave) . ' file berhasil disimpan.',
            'total_files' => count($finalFilesToSave),
            'files'       => array_map(fn($f) => ['name' => $f['original_name'], 'size' => $f['size'] ?? 0], $finalFilesToSave),
        ], 200);
    }

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


    /* ══════════════════════════════════════════════════════════════════════════
       BAGIAN 4: FUNGSI USER - EXPORT DATA
       ══════════════════════════════════════════════════════════════════════════ */

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
        $pdf = Pdf::loadView('admin.dokumen_masuk.pdf', compact('pengajuans'));

        return $pdf->download('Arsip_Bidang_' . Auth::user()->bidang->nama . '_' . time() . '.pdf');
    }

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
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
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
}
