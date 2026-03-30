<div class="epug-wrap">
    {{-- Header dengan tombol aksi --}}
    <div class="epug-header">
        <div class="epug-header-left">
            <h2 class="epug-title">Evaluasi Mandiri PUG</h2>
            <div class="epug-tahun-wrap">
                <label class="epug-tahun-label">Tahun</label>
                <select id="epugTahunSelect" class="epug-tahun-select">
                    @for($y = date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="epug-header-right">
            <button class="epug-btn epug-btn-excel" id="epugBtnExportExcel">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </button>
            <button class="epug-btn epug-btn-pdf" id="epugBtnExportPdf">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </button>
            <button class="epug-btn epug-btn-primary" id="epugBtnTambahPertanyaan">
                <i class="fa-solid fa-plus"></i> Tambah Pertanyaan
            </button>
        </div>
    </div>

    {{-- ── SCORING CARDS & CHART ── --}}
    <div class="epug-dashboard-top" style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 24px;">
        {{-- Card Total Skor Tunggal --}}
        <div class="epug-score-card" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 30px; background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.07);">
            <span class="epug-score-label" style="font-size: 1rem; color: #4b5563; font-weight:700;">Total Capaian PUG</span>
            <div class="epug-score-value" style="font-size: 3.5rem; font-weight:800; color: #067fb2; margin: 15px 0; line-height:1;">
                {{ number_format($totalSkor, 2) }}
            </div>
            <div class="epug-progress-wrap" style="width: 100%; background: #f3f4f6; height: 12px; border-radius: 20px; overflow: hidden;">
                <div class="epug-progress-fill" style="width:{{ $persenTotal }}%; background: #067fb2; height: 100%; transition: width 1s ease;"></div>
            </div>
            <span class="epug-score-maks" style="margin-top: 12px; font-size: 0.85rem; color:#6b7280; font-weight:600;">
                Maksimal: {{ number_format($totalMaks, 2) }} ({{ $persenTotal }}%)
            </span>
        </div>

        {{-- Wadah Chart ApexCharts --}}
        <div class="epug-score-card" style="padding: 20px; background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.07);">
            <span style="font-size:.9rem; font-weight:700; color:#111827;">Sebaran Skor per Komponen</span>
            <div id="epugComponentChart" style="min-height: 220px; margin-top: 10px;"></div>
        </div>
    </div>

    {{-- Data Tersembunyi untuk dibaca oleh Javascript Chart --}}
    <div id="epugChartData" data-categories="{{ json_encode($chartData['categories']) }}" data-series="{{ json_encode($chartData['series']) }}" style="display:none;"></div>

    {{-- ── TAB NAVIGASI ── --}}
    <div class="epug-tab-wrap">
        <button class="epug-tab active" data-tab="struktur">Struktur</button>
        <button class="epug-tab" data-tab="daftar">Daftar Komponen</button>
    </div>

    {{-- ── TREE: Komponen → Indikator → Pertanyaan ── --}}
    <div id="epugTabStruktur" class="epug-tab-content">
        @php
            $tahunData = $tahun ?? date('Y');
        @endphp

        @forelse($komponen ?? [] as $komp)
        <div class="epug-komponen-block">
            <div class="epug-komponen-header" data-toggle="komp-{{ $komp->id }}">
                <div class="epug-komponen-toggle">
                    <i class="fa-solid fa-chevron-down epug-chevron open" id="chevron-komp-{{ $komp->id }}"></i>
                    <span class="epug-komponen-label">{{ $komp->kode }}. {{ $komp->nama }}</span>
                </div>
                @php
                    $kompSkor = 0; $kompMaks = 0;
                    foreach($komp->indikator as $indk) {
                        foreach($indk->pertanyaan as $pert) {
                            $kompMaks += $pert->skor_maksimal;
                            $jwb = $pert->jawaban->first();
                            if($jwb) $kompSkor += $jwb->skor;
                        }
                    }
                @endphp
                <span class="epug-komponen-skor">Skor: {{ number_format($kompSkor, 2) }} / {{ number_format($kompMaks, 2) }}</span>
            </div>

            <div class="epug-komponen-body" id="komp-{{ $komp->id }}">
                @foreach($komp->indikator as $indk)
                <div class="epug-indikator-block">
                    <div class="epug-indikator-header" data-toggle="indk-{{ $indk->id }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-chevron-right epug-chevron-sm open" id="chevron-indk-{{ $indk->id }}"></i>
                            <span class="epug-indikator-label">{{ $indk->kode }}. {{ $indk->nama }}</span>
                        </div>
                    </div>

                    <div class="epug-indikator-body" id="indk-{{ $indk->id }}" style="display:block;">
                        @foreach($indk->pertanyaan as $pert)
                            @php
                                $jwb = $pert->jawaban->where('tahun', $tahunData)->first();
                                $status = $jwb ? $jwb->status : 'belum';
                                $statusClass = match($status) {
                                    'disetujui' => 'epug-status-green',
                                    'diisi'     => 'epug-status-blue',
                                    'ditolak'   => 'epug-status-red',
                                    default     => 'epug-status-red',
                                };
                                $statusLabel = match($status) {
                                    'disetujui' => 'Disetujui',
                                    'diisi'     => 'Sudah Diisi',
                                    'ditolak'   => 'Ditolak',
                                    default     => 'Belum Diisi',
                                };
                            @endphp
                        <div class="epug-pertanyaan-row" data-id="{{ $pert->id }}" data-tahun="{{ $tahunData }}" data-status="{{ $status }}">
                            <div class="epug-pertanyaan-left">
                                <i class="fa-regular fa-file-lines epug-pert-icon"></i>
                                <span class="epug-pert-text">
                                    {{ $pert->kode }}. {{ \Illuminate\Support\Str::limit($pert->pertanyaan, 90) }}
                                </span>
                            </div>
                            <div class="epug-pertanyaan-right">
                                <span class="epug-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="epug-skor-text">
                                    Skor: {{ number_format($jwb?->skor ?? 0, 2) }} / {{ number_format($pert->skor_maksimal, 2) }}
                                </span>
                                <button class="epug-btn-lihat epug-btn-lihat-trigger"
                                    data-pert-id="{{ $pert->id }}"
                                    data-tahun="{{ $tahunData }}">
                                    Lihat
                                </button>
                                @if(!in_array($status, ['disetujui', 'ditolak']))
                                <button class="epug-btn-icon epug-btn-edit-pert" title="Edit Pertanyaan"
                                    data-id="{{ $pert->id }}" 
                                    data-indikator="{{ $pert->indikator_id }}"
                                    data-kode="{{ $pert->kode }}"
                                    data-pertanyaan="{{ htmlspecialchars($pert->pertanyaan, ENT_QUOTES, 'UTF-8') }}"
                                    data-skor="{{ $pert->skor_maksimal }}"
                                    data-petunjuk="{{ htmlspecialchars($pert->petunjuk ?? '', ENT_QUOTES, 'UTF-8') }}"
                                    data-pilihan="{{ htmlspecialchars(json_encode($pert->pilihan_jawaban), ENT_QUOTES, 'UTF-8') }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @endif
                                
                                <button class="epug-btn-icon epug-btn-del-pert" title="Hapus Pertanyaan" data-id="{{ $pert->id }}">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="epug-empty">
            <i class="fa-regular fa-folder-open" style="font-size:2rem; color:#ccc;"></i>
            <p>Belum ada data komponen PUG.</p>
        </div>
        @endforelse
    </div>

    <div id="epugTabDaftar" class="epug-tab-content" style="display:none;">
        <p style="color:#6b7280; font-size:.875rem;">Tampilan daftar komponen akan ditampilkan di sini.</p>
    </div>
</div>

{{-- MODAL DETAIL & JAWAB PERTANYAAN --}}
<div id="epugModal" class="epug-modal-overlay" style="display:none;" role="dialog" aria-modal="true">
    <div class="epug-modal-dialog">
        <div class="epug-modal-header">
            <div>
                <div class="epug-modal-breadcrumb" id="epugModalBreadcrumb">Evaluasi / Evaluasi Mandiri / Entri</div>
                <h3 class="epug-modal-title" id="epugModalTitle">Jawab Pertanyaan</h3>
            </div>
            <button class="epug-modal-close" id="epugModalClose" aria-label="Tutup">&times;</button>
        </div>

        <div id="epugModalLoader" class="epug-modal-loader">
            <i class="fa-solid fa-spinner fa-spin fa-2x" style="color:#067fb2;"></i>
            <p>Memuat data...</p>
        </div>

        <div id="epugModalBody" class="epug-modal-body" style="display:none;">
            <div id="epugStatusAlert" class="epug-alert" style="display:none;"></div>
            <div id="epugPetunjukAlert" class="epug-alert" style="display:none; background:#f0f9ff; border:1px solid #bae6fd; color:#0369a1; margin-bottom:15px;">
                <i class="fa-solid fa-circle-info"></i>
                <span id="epugModalPetunjuk" style="margin-left:8px; line-height:1.4;"></span>
            </div>
            <div class="epug-modal-kode" id="epugModalKode"></div>

            <div class="epug-section">
                <h4 class="epug-section-title" style="display:flex; justify-content:space-between; align-items:center;">
                    Pilihan
                    <button type="button" class="epug-btn-icon" id="epugBtnClearPilihan" title="Bersihkan Pilihan" style="color:#ef4444; font-size:0.8rem; border:1px solid #ef4444; padding:2px 8px; border-radius:4px;">
                        <i class="fa-solid fa-eraser"></i> Bersihkan
                    </button>
                </h4>
                <div id="epugPilihanContainer" class="epug-pilihan-wrap"></div>
                <p id="epugErrJawaban" class="epug-field-error" style="display:none;"></p>
            </div>

            <div class="epug-section">
                <h4 class="epug-section-title">Catatan <span id="epugCatatanCounter" class="epug-counter">0/1000</span></h4>
                <textarea id="epugCatatan" class="epug-textarea" rows="5" placeholder="Tulis catatan pendukung jawaban..." maxlength="1100"></textarea>
                <p id="epugErrCatatan" class="epug-field-error" style="display:none;"></p>
            </div>

            <div class="epug-section">
                <h4 class="epug-section-title">Lampiran</h4>
                <div id="epugLampiranList" class="epug-lampiran-list"></div>
                <div class="epug-upload-zone" id="epugUploadZone">
                   <input type="file" id="epugFileInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" style="display:none;" multiple>
                    <label for="epugFileInput" class="epug-upload-label">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>Klik untuk upload (PDF, DOC, XLS, IMG — maks 10 MB)</span>
                    </label>
                </div>
                <p id="epugErrFile" class="epug-field-error" style="display:none;"></p>
            </div>

            <div id="epugSkorSection" class="epug-skor-section" style="display:none;">
                <div class="epug-skor-row">
                    <div>
                        <strong>Skor Tersimpan</strong>
                        <div id="epugSkorValue" class="epug-skor-value"></div>
                    </div>
                    <div>
                        <strong>Jawaban</strong>
                        <div id="epugJawabanSaved" class="epug-jawaban-saved"></div>
                    </div>
                </div>
            </div>

            <div id="epugAdminVerif" class="epug-admin-verif" style="display:none;">
                <h4 class="epug-section-title">Verifikasi Admin</h4>
                <textarea id="epugCatatanAdmin" class="epug-textarea" rows="3" placeholder="Tulis catatan admin (wajib diisi)..."></textarea>
                <p id="epugErrCatatanAdmin" class="epug-field-error" style="display:none;"></p>
                <div class="epug-verif-actions">
                    <button class="epug-btn epug-btn-danger" id="epugBtnTolak">
                        <i class="fa-solid fa-xmark"></i> Tolak
                    </button>
                    <button class="epug-btn epug-btn-success" id="epugBtnSetujui">
                        <i class="fa-solid fa-check"></i> Setujui
                    </button>
                </div>
            </div>

            <div class="epug-section">
                <button class="epug-collapse-btn" id="epugAuditToggle">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Perubahan
                    <i class="fa-solid fa-chevron-down" id="epugAuditChevron"></i>
                </button>
                <div id="epugAuditBody" class="epug-audit-body" style="display:none;">
                    <div id="epugAuditList"></div>
                </div>
            </div>
        </div>

        <div id="epugModalFooter" class="epug-modal-footer" style="display:none;">
            <button class="epug-btn epug-btn-secondary" id="epugBtnTutup">Tutup</button>
            <button class="epug-btn epug-btn-primary" id="epugBtnSimpan">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Jawaban
            </button>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / EDIT PERTANYAAN (ADMIN) --}}
<div id="epugModalTambah" class="epug-modal-overlay" style="display:none;">
    <div class="epug-modal-dialog" style="max-width:600px;">
        <div class="epug-modal-header">
            <h3 class="epug-modal-title">Tambah Pertanyaan</h3>
            <button class="epug-modal-close" id="epugModalTambahClose">&times;</button>
        </div>
        <div class="epug-modal-body">
            <div class="epug-form-group">
                <label class="epug-label">Indikator <span class="epug-required">*</span></label>
                <select id="epugTambahIndikator" class="epug-select">
                    <option value="">-- Pilih Indikator --</option>
                    @foreach($komponen ?? [] as $komp)
                        @foreach($komp->indikator as $indk)
                            <option value="{{ $indk->id }}">{{ $indk->kode }}. {{ $indk->nama }}</option>
                        @endforeach
                    @endforeach
                </select>
                <p id="epugErrIndikator_id" class="epug-field-error" style="display:none;"></p>
            </div>
            <div class="epug-form-group">
                <label class="epug-label">Kode <span class="epug-required">*</span></label>
                <input type="text" id="epugTambahKode" class="epug-input" placeholder="e.g. 1.1" maxlength="20">
                <p id="epugErrKode" class="epug-field-error" style="display:none;"></p>
            </div>
            <div class="epug-form-group">
                <label class="epug-label">Pertanyaan <span class="epug-required">* (Maks 50 Karakter)</span></label>
                <textarea id="epugTambahPertanyaan" class="epug-textarea" rows="2" placeholder="Tulis pertanyaan..." maxlength="50"></textarea>
                <p id="epugErrPertanyaan" class="epug-field-error" style="display:none;"></p>
            </div>
            <div class="epug-form-group">
                <label class="epug-label">Skor Maksimal <span class="epug-required">* (Maks 100)</span></label>
                <input type="number" id="epugTambahSkorMaks" class="epug-input" min="0" max="100" step="0.01">
                <p id="epugErrSkor_maksimal" class="epug-field-error" style="display:none;"></p>
            </div>
            <div class="epug-form-group">
                <label class="epug-label">Pilihan Jawaban <span class="epug-required">*</span></label>
                <div id="epugPilihanBuilder">
                    <div class="epug-pilihan-item">
                        <input type="text" class="epug-input epug-pilihan-label" placeholder="Label jawaban" maxlength="200">
                        <input type="number" class="epug-input epug-pilihan-skor" placeholder="Skor" min="0" max="100" step="0.01" style="width:100px;">
                    </div>
                </div>
                <p id="epugErrPilihan_jawaban" class="epug-field-error" style="display:none; margin-bottom:8px;"></p>
                <button type="button" class="epug-btn-add-pilihan" id="epugBtnAddPilihan">
                    <i class="fa-solid fa-plus"></i> Tambah Pilihan
                </button>
            </div>
            <div class="epug-form-group">
                <label class="epug-label">Petunjuk (opsional)</label>
                <textarea id="epugTambahPetunjuk" class="epug-textarea" rows="2" placeholder="Tulis petunjuk pengisian..." maxlength="2000"></textarea>
                <p id="epugErrPetunjuk" class="epug-field-error" style="display:none;"></p>
            </div>
        </div>
        <div class="epug-modal-footer">
            <button class="epug-btn epug-btn-secondary" id="epugModalTambahCancel">Batal</button>
            <button class="epug-btn epug-btn-primary" id="epugBtnSimpanPertanyaan">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
        </div>
    </div>
</div>