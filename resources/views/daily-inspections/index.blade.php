@extends('layouts.safetrack', ['title' => 'Inspeksi Harian', 'pageTitle' => 'Inspeksi Harian'])

@section('content')
<header class="page-head">
    <div>
        <span class="eyebrow">AKTIVITAS KESELAMATAN</span>
        <h1>Inspeksi harian</h1>
        <p>{{ $isAdmin ? 'Lihat dan verifikasi hasil inspeksi yang dikirim akun pribadi pengguna.' : 'Periksa kondisi area, dokumentasikan temuan, dan pantau tindak lanjutnya.' }}</p>
    </div>
    @unless($isAdmin)
        <div>
            <button type="button" class="btn primary" onclick="openInspectionModal()">+ Buat inspeksi</button>
        </div>
    @endunless
</header>

<!-- Stats Overview Cards -->
<section class="stats" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:24px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px 20px;">
        <span style="font-size:12px;color:#64748b;display:block;">Total inspeksi</span>
        <strong style="font-size:24px;color:#0f172a;display:block;margin:4px 0;">{{ $inspections->total() }}</strong>
        <small style="color:#94a3b8;font-size:11px;">Checklist terdokumentasi</small>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px 20px;">
        <span style="font-size:12px;color:#64748b;display:block;">Menunggu review</span>
        <strong style="font-size:24px;color:#0f172a;display:block;margin:4px 0;">{{ collect($inspections->items())->where('status', 'submitted')->count() }}</strong>
        <small style="color:#94a3b8;font-size:11px;">Perlu pemeriksaan Admin</small>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:18px 20px;">
        <span style="font-size:12px;color:#64748b;display:block;">Selesai</span>
        <strong style="font-size:24px;color:#0f172a;display:block;margin:4px 0;">{{ collect($inspections->items())->where('status', 'closed')->count() }}</strong>
        <small style="color:#94a3b8;font-size:11px;">Inspeksi ditutup</small>
    </div>
</section>

<!-- Inspection List Panel -->
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Daftar inspeksi</h2>
            <p>{{ $isAdmin ? 'Seluruh inspeksi di lingkungan kerja.' : 'Riwayat inspeksi yang Anda kirim.' }}</p>
        </div>
        <span class="badge">{{ $inspections->total() }} inspeksi</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Area kerja</th>
                    <th>Pelaksana</th>
                    <th>Shift</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $inspection)
                    <tr>
                        <td>
                            <strong>{{ $inspection->workArea?->name ?? 'Area dihapus' }}</strong>
                            <small>#{{ $inspection->id }}</small>
                        </td>
                        <td>{{ $inspection->inspector?->name ?? '-' }}</td>
                        <td>{{ $inspection->shift?->name ?? '-' }}</td>
                        <td>{{ $inspection->inspection_date ? \Carbon\Carbon::parse($inspection->inspection_date)->translatedFormat('d M Y') : '-' }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    'submitted' => ['Menunggu Review', 'badge'],
                                    'reviewed' => ['Direview', 'badge green'],
                                    'closed' => ['Selesai', 'badge gray'],
                                ];
                                [$label, $badgeClass] = $statusMap[$inspection->status] ?? [$inspection->status, 'badge'];
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty">Belum ada inspeksi yang dikirim.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($inspections->hasPages())
        <div style="padding: 16px 20px;">
            {{ $inspections->links() }}
        </div>
    @endif
</section>
@endsection

@unless($isAdmin)
@push('modals')
<!-- Modal Buat Inspeksi Harian -->
<div id="inspectionModal" class="modal-layer" role="presentation" style="display:none;" onclick="if(event.target === this) closeInspectionModal()">
    <section class="modal wide" role="dialog" aria-modal="true" aria-labelledby="insp-modal-title" style="max-width:760px;width:95vw;">
        <header>
            <h2 id="insp-modal-title">Buat inspeksi harian</h2>
            <button type="button" aria-label="Tutup" onclick="closeInspectionModal()">✕</button>
        </header>
        <div class="modal-body">
            <div class="wizard-progress" style="margin-bottom:24px;">
                <span id="inspStep1Ind" class="active">1</span>
                <i></i>
                <span id="inspStep2Ind">2</span>
                <i></i>
                <span id="inspStep3Ind">3</span>
            </div>

            <div id="loadErrorMsg" class="alert error" style="display:none;"></div>

            <form id="inspectionForm" method="POST" action="{{ route('daily-inspections.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Step 1: Area & Shift Context -->
                <div id="inspStep1">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <label style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                            Area kerja *
                            <select id="inspWorkArea" name="work_area_id" required style="width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;background:#fff;">
                                <option value="">Pilih area yang diperiksa</option>
                                @foreach($workAreas as $area)
                                    <option value="{{ $area->id }}" data-url="{{ route('work-areas.active-questions', $area) }}" @selected(old('work_area_id') == $area->id)>{{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('work_area_id')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                        </label>

                        <label style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                            Shift kerja *
                            <select id="inspShift" name="shift_id" required style="width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;background:#fff;">
                                <option value="">Pilih shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                            @error('shift_id')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                        </label>

                        <label style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                            Tanggal inspeksi *
                            <input type="date" name="inspection_date" max="{{ now()->toDateString() }}" value="{{ old('inspection_date', now()->toDateString()) }}" required style="width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;">
                            @error('inspection_date')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                        </label>

                        <label style="grid-column:1/-1;display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                            Catatan tambahan
                            <textarea name="notes" placeholder="Opsional: lokasi spesifik atau kondisi umum area" style="width:100%;min-height:76px;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;resize:vertical;">{{ old('notes') }}</textarea>
                            @error('notes')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                        </label>
                    </div>

                    <div style="margin-top:16px;padding:14px;background:#eff6ff;border:1px solid #cfe0f5;border-radius:7px;color:#54708f;font-size:12.5px;">
                        <strong>Checklist mengikuti area Anda</strong><br>
                        Pertanyaan dimuat dari data master aktif dan diurutkan otomatis.
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                        <button class="btn ghost" type="button" onclick="closeInspectionModal()">Batal</button>
                        <button id="btnLoadQuestions" class="btn primary" type="button" onclick="loadQuestionsAndProceed()">Lanjut ke checklist →</button>
                    </div>
                </div>

                <!-- Step 2: Checklist Questions -->
                <div id="inspStep2" style="display:none;">
                    <div id="questionsContainer" style="display:flex;flex-direction:column;gap:18px;max-height:50vh;overflow-y:auto;padding-right:8px;">
                        <!-- Dynamically populated -->
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:20px;">
                        <button class="btn ghost" type="button" onclick="goToInspStep(1)">← Kembali</button>
                        <button class="btn primary" type="button" onclick="validateChecklistAndProceed()">Lanjut ke konfirmasi →</button>
                    </div>
                </div>

                <!-- Step 3: Final Confirmation -->
                <div id="inspStep3" style="display:none;">
                    <div style="margin-bottom:18px;">
                        <h3 style="font-size:16px;color:#0f172a;margin-bottom:6px;">Konfirmasi Hasil Inspeksi</h3>
                        <p style="color:#64748b;font-size:12.5px;">Pastikan seluruh jawaban telah sesuai dengan kondisi aktual di lapangan.</p>
                    </div>

                    <div id="summaryPreview" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;font-size:13px;margin-bottom:20px;">
                        <!-- Summary here -->
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:20px;">
                        <button class="btn ghost" type="button" onclick="goToInspStep(2)">← Kembali</button>
                        <button class="btn primary" type="submit">Kirim inspeksi</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endpush

@push('scripts')
<script>
    const inspModal = document.getElementById('inspectionModal');
    const step1 = document.getElementById('inspStep1');
    const step2 = document.getElementById('inspStep2');
    const step3 = document.getElementById('inspStep3');
    const ind1 = document.getElementById('inspStep1Ind');
    const ind2 = document.getElementById('inspStep2Ind');
    const ind3 = document.getElementById('inspStep3Ind');
    const areaSelect = document.getElementById('inspWorkArea');
    const shiftSelect = document.getElementById('inspShift');
    const questionsContainer = document.getElementById('questionsContainer');
    const errorBox = document.getElementById('loadErrorMsg');
    let loadedQuestions = [];

    function openInspectionModal() {
        if (!inspModal) return;
        inspModal.style.display = 'flex';
        goToInspStep(1);
    }

    function closeInspectionModal() {
        if (!inspModal) return;
        inspModal.style.display = 'none';
        goToInspStep(1);
    }

    function goToInspStep(step) {
        step1.style.display = step === 1 ? 'block' : 'none';
        step2.style.display = step === 2 ? 'block' : 'none';
        step3.style.display = step === 3 ? 'block' : 'none';

        ind1.className = step === 1 ? 'active' : (step > 1 ? 'done' : '');
        ind2.className = step === 2 ? 'active' : (step > 2 ? 'done' : '');
        ind3.className = step === 3 ? 'active' : '';
    }

    async function loadQuestionsAndProceed() {
        errorBox.style.display = 'none';
        if (!areaSelect.value) {
            areaSelect.focus();
            areaSelect.reportValidity();
            return;
        }
        if (!shiftSelect.value) {
            shiftSelect.focus();
            shiftSelect.reportValidity();
            return;
        }

        const option = areaSelect.selectedOptions[0];
        const url = option.dataset.url;
        questionsContainer.innerHTML = '<p style="text-align:center;color:#64748b;padding:24px;">Memuat pertanyaan...</p>';
        goToInspStep(2);

        try {
            const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error();
            const data = await res.json();
            loadedQuestions = data.data || [];

            if (!loadedQuestions.length) {
                questionsContainer.innerHTML = '<div class="alert error">Belum ada pertanyaan aktif untuk area ini. Hubungi Admin.</div>';
                return;
            }

            questionsContainer.innerHTML = loadedQuestions.map((q, idx) => `
                <div style="border:1px solid #e2e8f0;border-radius:8px;padding:14px;background:#fff;">
                    <div style="font-weight:600;color:#0f172a;margin-bottom:10px;">${idx + 1}. ${escapeHtml(q.question)}</div>
                    <input type="hidden" name="answers[${idx}][question_id]" value="${q.id}">
                    <div style="display:flex;gap:20px;margin-bottom:10px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;color:#15803d;">
                            <input type="radio" name="answers[${idx}][answer]" value="yes" required onchange="toggleConditionField(${idx}, false)"> Ya (Sesuai)
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;color:#b42318;">
                            <input type="radio" name="answers[${idx}][answer]" value="no" required onchange="toggleConditionField(${idx}, true)"> Tidak (Ada Temuan)
                        </label>
                    </div>
                    <div id="condition_${idx}" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:12px;margin-top:10px;">
                        <label style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px;font-size:12px;font-weight:600;color:#991b1b;">
                            Deskripsi kondisi temuan *
                            <textarea name="answers[${idx}][description]" placeholder="Jelaskan kondisi ketidaksesuaian yang ditemukan" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;"></textarea>
                        </label>
                        <label style="display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:600;color:#991b1b;">
                            Foto kondisi temuan *
                            <input type="file" accept="image/*" name="answers[${idx}][photo]" style="font-size:12px;">
                        </label>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            questionsContainer.innerHTML = '<div class="alert error">Pertanyaan gagal dimuat. Periksa koneksi dan coba kembali.</div>';
        }
    }

    function toggleConditionField(index, show) {
        const box = document.getElementById(`condition_${index}`);
        if (!box) return;
        box.style.display = show ? 'block' : 'none';
        const txt = box.querySelector('textarea');
        const file = box.querySelector('input[type="file"]');
        if (txt) txt.required = show;
        if (file) file.required = show;
    }

    function validateChecklistAndProceed() {
        for (let i = 0; i < loadedQuestions.length; i++) {
            const checked = document.querySelector(`input[name="answers[${i}][answer]"]:checked`);
            if (!checked) {
                alert(`Harap isi jawaban untuk pertanyaan nomor ${i + 1}`);
                return;
            }
            if (checked.value === 'no') {
                const desc = document.querySelector(`textarea[name="answers[${i}][description]"]`);
                const photo = document.querySelector(`input[name="answers[${i}][photo]"]`);
                if (!desc || !desc.value.trim()) {
                    desc.focus();
                    desc.reportValidity();
                    return;
                }
                if (!photo || !photo.files.length) {
                    alert(`Foto kondisi wajib diunggah untuk pertanyaan nomor ${i + 1}`);
                    return;
                }
            }
        }

        // Render summary
        const areaName = areaSelect.selectedOptions[0]?.text || '';
        const shiftName = shiftSelect.selectedOptions[0]?.text || '';
        let yesCount = 0;
        let noCount = 0;
        for (let i = 0; i < loadedQuestions.length; i++) {
            const val = document.querySelector(`input[name="answers[${i}][answer]"]:checked`)?.value;
            if (val === 'yes') yesCount++; else noCount++;
        }

        document.getElementById('summaryPreview').innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                <div><strong>Area:</strong> ${escapeHtml(areaName)}</div>
                <div><strong>Shift:</strong> ${escapeHtml(shiftName)}</div>
                <div><strong>Total Pertanyaan:</strong> ${loadedQuestions.length}</div>
                <div><strong>Temuan:</strong> <span style="color:${noCount > 0 ? '#b42318' : '#15803d'};font-weight:700;">${noCount} Temuan (${yesCount} Sesuai)</span></div>
            </div>
        `;

        goToInspStep(3);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    @if(!empty($openCreateModal))
        window.addEventListener('DOMContentLoaded', () => {
            openInspectionModal();
        });
    @endif
</script>
@endpush
@endunless
