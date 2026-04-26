<?php

namespace App\Http\Controllers;

use App\Models\PugKomponen;
use App\Models\PugPertanyaan;
use App\Models\PugJawaban;
use App\Models\PugJawabanLampiran;
use App\Models\PugAuditLog;
use App\Models\PugJawabanVersi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DataExport;
use Maatwebsite\Excel\Facades\Excel;

class EvaluasiPugController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // SPA CONTENT ENDPOINT (INDEX)
    // ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        // Pastikan tahun dikonversi menjadi integer agar filter database akurat
        $tahun = (int) $request->get('tahun', date('Y'));

        $komponen = PugKomponen::with([
            'indikator.pertanyaan.jawaban' => function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            }
        ])->where('aktif', true)->orderBy('urutan')->get();

        // Variabel untuk menyimpan kalkulasi Total Skor & Grafik
        $totalSkor = 0;
        $totalMaks = 0;
        $chartData = [
            'categories' => [],
            'series'     => []
        ];

        foreach ($komponen as $komp) {
            $skorKomp = 0;
            foreach ($komp->indikator as $indk) {
                foreach ($indk->pertanyaan as $pert) {
                    $totalMaks += $pert->skor_maksimal;

                    // Filter ulang secara ketat berdasarkan tahun
                    $jwb = $pert->jawaban->where('tahun', $tahun)->first();
                    if ($jwb && in_array($jwb->status, ['diisi', 'disetujui'])) {
                        $skorKomp += $jwb->skor;
                        $totalSkor += $jwb->skor;
                    }
                }
            }
            // Masukkan data per komponen ke grafik (Misal: "A", "B", dll)
            $chartData['categories'][] = 'Komp ' . $komp->kode;
            $chartData['series'][]     = round($skorKomp, 2);
        }

        $persenTotal = $totalMaks > 0 ? round(($totalSkor / $totalMaks) * 100, 1) : 0;

        return view('admin.evaluasi_pug.index', compact(
            'komponen',
            'tahun',
            'totalSkor',
            'totalMaks',
            'persenTotal',
            'chartData'
        ));
    }

    // ──────────────────────────────────────────────────────────────────
    // DETAIL PERTANYAAN (untuk modal)
    // GET /admin/evaluasi-pug/pertanyaan/{id}?tahun=2025
    // ──────────────────────────────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $tahun      = $request->get('tahun', date('Y'));
        $pertanyaan = PugPertanyaan::with(['indikator.komponen'])->findOrFail($id);
        $jawaban    = PugJawaban::with(['lampiran', 'diisiOleh', 'diverifikasiOleh'])
            ->where('pertanyaan_id', $id)
            ->where('tahun', $tahun)
            ->first();
        $versi = PugJawabanVersi::with('user')
            ->where('jawaban_id', optional($jawaban)->id)
            ->orderByDesc('versi')
            ->get();
        $auditLog = PugAuditLog::with('user')
            ->where('jawaban_id', optional($jawaban)->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'pertanyaan' => $pertanyaan,
            'jawaban'    => $jawaban,
            'versi'      => $versi,
            'audit_log'  => $auditLog,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // SIMPAN JAWABAN
    // ──────────────────────────────────────────────────────────────────
    public function simpanJawaban(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan_id' => 'required|exists:pug_pertanyaan,id',
            'tahun'         => 'required|integer|min:2020|max:2030',
            'jawaban_kode'  => 'nullable|string|max:100',
            'jawaban_label' => 'nullable|string|max:500',
            'catatan'       => 'nullable|string|max:1000|regex:/^[^<>{}\[\]\\\\]*$/',
            'skor'          => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        DB::transaction(function () use ($request) {
            $jawaban = PugJawaban::firstOrNew([
                'pertanyaan_id' => $request->pertanyaan_id,
                'tahun'         => $request->tahun,
            ]);

            if ($jawaban->exists && $jawaban->status === 'disetujui') {
                throw new \Exception('Jawaban yang sudah disetujui tidak dapat diubah.');
            }

            $sebelum = $jawaban->exists ? $jawaban->toArray() : null;
            $isNew = !$jawaban->exists;

            $statusBaru = empty($request->jawaban_kode) ? 'belum' : 'diisi';

            $jawaban->fill([
                'jawaban_kode'  => $request->jawaban_kode,
                'jawaban_label' => $request->jawaban_label,
                'catatan'       => $request->catatan,
                'skor'          => $request->skor ?? 0,
                'status'        => $statusBaru,
                'diisi_oleh'    => Auth::id(),
            ])->save();

            $versiTerbaru = PugJawabanVersi::where('jawaban_id', $jawaban->id)->max('versi') ?? 0;
            PugJawabanVersi::create([
                'jawaban_id'    => $jawaban->id,
                'user_id'       => Auth::id(),
                'versi'         => $versiTerbaru + 1,
                'jawaban_kode'  => $request->jawaban_kode,
                'jawaban_label' => $request->jawaban_label,
                'catatan'       => $request->catatan,
                'skor'          => $request->skor ?? 0,
                'status'        => $statusBaru,
            ]);

            PugAuditLog::create([
                'jawaban_id' => $jawaban->id,
                'user_id'    => Auth::id(),
                'aksi'       => $isNew ? 'isi_jawaban' : 'ubah_jawaban',
                'sebelum'    => $sebelum,
                'sesudah'    => $jawaban->toArray(),
                'ip_address' => request()->ip(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Jawaban berhasil disimpan.']);
    }

    // ──────────────────────────────────────────────────────────────────
    // UPLOAD LAMPIRAN
    // ──────────────────────────────────────────────────────────────────
    public function uploadLampiran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pertanyaan_id' => 'required|exists:pug_pertanyaan,id',
            'tahun'         => 'required|integer',
            'file'          => 'required|array|max:10', // ✅ Maksimal 10 file per kirim
            'file.*'        => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        ], [
            'file.*.max'   => 'Masing-masing file maksimal 10 MB.',
            'file.*.mimes' => 'Terdapat format file yang tidak didukung.',
            'file.max'     => 'Anda hanya dapat mengunggah maksimal 10 file sekaligus.'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $jawaban = PugJawaban::firstOrCreate([
            'pertanyaan_id' => $request->pertanyaan_id,
            'tahun'         => $request->tahun,
        ]);

        if ($jawaban->status === 'disetujui') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah jawaban yang sudah disetujui.'], 403);
        }

        $jumlahLampiranSaatIni = PugJawabanLampiran::where('jawaban_id', $jawaban->id)->count();
        $jumlahUpload = count($request->file('file'));

        // ✅ Validasi jika total file yg ada + file yg baru diupload > 10
        if ($jumlahLampiranSaatIni + $jumlahUpload > 10) {
            return response()->json([
                'success' => false,
                'message' => 'Batas maksimal tercapai! Total lampiran tidak boleh lebih dari 10 file (Saat ini: ' . $jumlahLampiranSaatIni . ' file).'
            ], 400);
        }

        $lampirans = [];
        foreach ($request->file('file') as $file) {
            $namaAsli  = $file->getClientOriginalName();
            $safeName  = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $namaAsli);
            $path      = $file->storeAs('pug_lampiran', $safeName, 'public');

            $lampiran = PugJawabanLampiran::create([
                'jawaban_id'     => $jawaban->id,
                'nama_file'      => $namaAsli,
                'path_file'      => $path,
                'mime_type'      => $file->getMimeType(),
                'ukuran'         => $file->getSize(),
                'diupload_oleh'  => Auth::id(),
            ]);
            $lampiran->url = Storage::url($path);
            $lampirans[] = $lampiran;
        }

        return response()->json([
            'success'    => true,
            'lampirans'  => $lampirans, // ✅ Kirim balik semua file yg diupload
            'jawaban_id' => $jawaban->id
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // HAPUS LAMPIRAN
    // DELETE /admin/evaluasi-pug/lampiran/{id}
    // ──────────────────────────────────────────────────────────────────
    public function hapusLampiran($id)
    {
        $lampiran = PugJawabanLampiran::findOrFail($id);
        if ($lampiran->jawaban->status === 'disetujui') {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah jawaban yang disetujui.'], 403);
        }
        if (Storage::disk('public')->exists($lampiran->path_file)) {
            Storage::disk('public')->delete($lampiran->path_file);
        }
        $lampiran->delete();
        return response()->json(['success' => true]);
    }

    // ──────────────────────────────────────────────────────────────────
    // ADMIN: VERIFIKASI (SETUJUI / TOLAK)
    // POST /admin/evaluasi-pug/verifikasi
    // ──────────────────────────────────────────────────────────────────
    public function verifikasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jawaban_id'     => 'required|exists:pug_jawaban,id',
            'aksi'           => 'required|in:disetujui,ditolak',
            'catatan_admin'  => 'required|string|min:5|max:1000',
        ], [
            'catatan_admin.required' => 'Catatan admin wajib diisi saat menyetujui/menolak.',
            'catatan_admin.min'      => 'Catatan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $jawaban = PugJawaban::findOrFail($request->jawaban_id);
        $sebelum = $jawaban->toArray();

        $jawaban->update([
            'status'               => $request->aksi,
            'catatan_admin'        => $request->catatan_admin,
            'diverifikasi_oleh'    => Auth::id(),
            'diverifikasi_at'      => now(),
        ]);

        PugAuditLog::create([
            'jawaban_id' => $jawaban->id,
            'user_id'    => Auth::id(),
            'aksi'       => $request->aksi === 'disetujui' ? 'setujui' : 'tolak',
            'sebelum'    => $sebelum,
            'sesudah'    => $jawaban->fresh()->toArray(),
            'ip_address' => request()->ip(),
        ]);

        return response()->json(['success' => true, 'message' => 'Verifikasi berhasil.']);
    }

    // ──────────────────────────────────────────────────────────────────
    // TAMBAH PERTANYAAN (ADMIN)
    // POST /admin/evaluasi-pug/pertanyaan
    // ──────────────────────────────────────────────────────────────────
    public function tambahPertanyaan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indikator_id'    => 'required|exists:pug_indikator,id',
            'kode'            => 'required|string|max:20|regex:/^[0-9.]+$/|unique:pug_pertanyaan,kode',
            'pertanyaan'      => 'required|string|max:100|unique:pug_pertanyaan,pertanyaan',
            'skor_maksimal'   => 'required|numeric|min:0|max:100',
            'pilihan_jawaban' => 'required|array|min:1',
            'petunjuk'        => 'nullable|string|max:500',
        ], [
            'kode.regex' => 'Kode hanya boleh berisi angka dan titik (tanpa spasi, huruf, atau simbol lain).',
            'kode.unique' => 'Kode pertanyaan sudah digunakan, silakan gunakan kode lain.',
            'pertanyaan.unique' => 'Pertanyaan ini sudah ada di dalam sistem.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $pertanyaan = PugPertanyaan::create([
            'indikator_id'    => $request->indikator_id,
            'kode'            => $request->kode,
            'pertanyaan'      => $request->pertanyaan,
            'skor_maksimal'   => $request->skor_maksimal,
            'pilihan_jawaban' => $request->pilihan_jawaban,
            'petunjuk'        => $request->petunjuk,
            'urutan'          => PugPertanyaan::where('indikator_id', $request->indikator_id)->max('urutan') + 1,
        ]);

        return response()->json(['success' => true, 'pertanyaan' => $pertanyaan]);
    }

    // ──────────────────────────────────────────────────────────────────
    // EDIT PERTANYAAN (ADMIN)
    // ──────────────────────────────────────────────────────────────────
    public function updatePertanyaan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'indikator_id'    => 'required|exists:pug_indikator,id',
            'kode'            => 'required|string|max:20|regex:/^[0-9.]+$/|unique:pug_pertanyaan,kode,' . $id,
            'pertanyaan'      => 'required|string|max:100|unique:pug_pertanyaan,pertanyaan,' . $id,
            'skor_maksimal'   => 'required|numeric|min:0|max:100',
            'pilihan_jawaban' => 'required|array|min:1',
            'petunjuk'        => 'nullable|string|max:500',
        ], [
            'kode.regex' => 'Kode hanya boleh berisi angka dan titik (tanpa spasi, huruf, atau simbol lain).',
            'kode.unique' => 'Kode pertanyaan sudah digunakan, silakan gunakan kode lain.',
            'pertanyaan.unique' => 'Pertanyaan ini sudah ada di dalam sistem.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        // ✅ ATURAN BARU: Tidak bisa edit jika ada jawaban yang Diterima atau Ditolak
        $adaJawabanTerkunci = PugJawaban::where('pertanyaan_id', $id)
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->exists();

        if ($adaJawabanTerkunci) {
            return response()->json(['success' => false, 'message' => 'Pertanyaan tidak dapat diedit karena sudah memiliki jawaban yang disetujui atau ditolak.'], 403);
        }

        $pertanyaan = PugPertanyaan::findOrFail($id);
        $pertanyaan->update([
            'indikator_id'    => $request->indikator_id,
            'kode'            => $request->kode,
            'pertanyaan'      => $request->pertanyaan,
            'skor_maksimal'   => $request->skor_maksimal,
            'pilihan_jawaban' => $request->pilihan_jawaban,
            'petunjuk'        => $request->petunjuk,
        ]);

        return response()->json(['success' => true, 'pertanyaan' => $pertanyaan]);
    }

    // ──────────────────────────────────────────────────────────────────
    // HAPUS PERTANYAAN (ADMIN)
    // ──────────────────────────────────────────────────────────────────
    public function hapusPertanyaan($id)
    {
        // ✅ ATURAN BARU: Selalu bisa dihapus apapun statusnya.
        // Data jawaban otomatis akan ikut terhapus karena foreign key onDelete('cascade')
        $pertanyaan = PugPertanyaan::findOrFail($id);
        $pertanyaan->delete();
        return response()->json(['success' => true]);
    }

    // ──────────────────────────────────────────────────────────────────
    // EXPORT EXCEL
    // GET /admin/evaluasi-pug/export/excel?tahun=2025
    // ──────────────────────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $tahun   = $request->get('tahun', date('Y'));
        $komponen = PugKomponen::with([
            'indikator.pertanyaan.jawaban' => fn($q) => $q->where('tahun', $tahun)->with('lampiran'),
        ])->where('aktif', true)->orderBy('urutan')->get();

        $data = [];
        foreach ($komponen as $komp) {
            foreach ($komp->indikator as $indk) {
                foreach ($indk->pertanyaan as $pert) {
                    $jwb = $pert->jawaban->first();
                    $data[] = [
                        $pert->kode,
                        $komp->nama,
                        $indk->nama,
                        $pert->pertanyaan,
                        $jwb?->jawaban_label ?? '-',
                        $jwb?->skor ?? 0,
                        $pert->skor_maksimal,
                        $jwb?->status ?? 'belum',
                        $jwb?->catatan ?? '-',
                        $jwb?->diisiOleh?->name ?? '-',
                        $jwb?->created_at?->format('d/m/Y H:i') ?? '-',
                        $jwb?->lampiran->count() . ' file',
                    ];
                }
            }
        }

        $headings = ['Kode', 'Komponen', 'Indikator', 'Pertanyaan', 'Jawaban', 'Skor', 'Skor Maks', 'Status', 'Catatan', 'Diisi Oleh', 'Tanggal Isi', 'Lampiran'];
        $fileName = 'hasil-input-evaluasi-mandiri-' . $tahun . '.xlsx'; // Ekstensi .xlsx murni

        return Excel::download(new DataExport($data, $headings), $fileName);
    }

    // ──────────────────────────────────────────────────────────────────
    // EXPORT PDF
    // GET /admin/evaluasi-pug/export/pdf?tahun=2025
    // ──────────────────────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $tahun   = $request->get('tahun', date('Y'));
        $komponen = PugKomponen::with([
            'indikator.pertanyaan.jawaban' => fn($q) => $q->where('tahun', $tahun)->with('lampiran'),
        ])->where('aktif', true)->orderBy('urutan')->get();

        $pdf = Pdf::loadView('admin.evaluasi_pug.export_pdf', compact('komponen', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('evaluasi-pug-' . $tahun . '.pdf');
    }
}
