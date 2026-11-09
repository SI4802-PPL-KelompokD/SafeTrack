@extends('layouts.safetrack', ['title' => 'Kelola Area Kerja', 'pageTitle' => 'Kelola Area Kerja'])

@section('content')
<header class="page-head">
    <div>
        <span class="eyebrow">ADMINISTRASI</span>
        <h1>Data master</h1>
        <p>Satu referensi data untuk aktivitas keselamatan yang konsisten.</p>
    </div>
    <div>
        <button class="btn primary" type="button" onclick="openCreateModal()">+ Tambah area</button>
    </div>
</header>

<!-- Tabs Nav Bar -->
<div class="master-tabs-bar">
    <a href="{{ route('admin.departments.index') }}" class="master-tab-item">
        <span>Departemen</span> <span class="tab-badge">2</span>
    </a>
    <a href="{{ route('shifts.index') }}" class="master-tab-item">
        <span>Shift kerja</span>
    </a>
    <button type="button" class="master-tab-item" onclick="alert('Modul Kategori Temuan dalam integrasi Data Master.')">
        <span>Kategori temuan</span> <span class="tab-badge">2</span>
    </button>
    <a href="{{ route('admin.work-areas.index') }}" class="master-tab-item active">
        <span>Area &amp; pertanyaan</span> <span class="tab-badge">{{ $workAreas->total() }}</span>
    </a>
</div>

<!-- Main Table Panel -->
<section class="panel">
    <form class="toolbar filter-toolbar" method="GET" action="{{ route('admin.work-areas.index') }}">
        <label class="filter-search">
            <span aria-hidden="true">🔍</span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama area...">
        </label>
        <button class="btn" type="submit">Terapkan</button>
        <a class="btn ghost" href="{{ route('admin.work-areas.index') }}">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Area kerja</th>
                    <th>Pertanyaan</th>
                    <th>Diperbarui</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workAreas as $area)
                    <tr>
                        <td><strong>{{ $area->name }}</strong></td>
                        <td>{{ $area->questions_count }}</td>
                        <td>{{ $area->updated_at ? $area->updated_at->format('d M Y') : '-' }}</td>
                        <td>
                            <div class="row-actions">
                                <a class="action-btn action-edit" href="{{ route('admin.work-areas.show', $area) }}">Ubah</a>
                                <button type="button" class="action-btn action-delete" onclick="openDeleteModal({{ $area->id }}, @js($area->name))">Hapus</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty">Tidak ada area kerja yang cocok.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($workAreas->hasPages())
        <div style="padding: 16px 20px;">
            {{ $workAreas->links() }}
        </div>
    @endif
</section>
@endsection

@push('modals')
<!-- Modal Tambah Area (2-Step Wizard) -->
<div id="createAreaModal" class="modal-layer" role="presentation" style="display:none;" onclick="if(event.target === this) closeCreateModal()">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="create-modal-title">
        <header>
            <h2 id="create-modal-title">Tambah area kerja</h2>
            <button type="button" aria-label="Tutup" onclick="closeCreateModal()">✕</button>
        </header>
        <div class="modal-body">
            <div class="wizard-progress">
                <span id="step1Indicator" class="active">1</span>
                <i></i>
                <span id="step2Indicator">2</span>
            </div>

            <form id="createAreaForm" method="POST" action="{{ route('admin.work-areas.store') }}">
                @csrf

                <!-- Step 1: Area Info -->
                <div id="wizardStep1" class="form-grid">
                    <label class="full" style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                        Nama area kerja *
                        <input id="createName" name="name" value="{{ old('name') }}" placeholder="Contoh: Workshop Utama" style="width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;" required>
                        @error('name')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                    </label>
                    <label class="full" style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                        Deskripsi
                        <textarea id="createDescription" name="description" placeholder="Jelaskan lokasi atau fungsi area kerja." style="width:100%;min-height:92px;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;resize:vertical;">{{ old('description') }}</textarea>
                        @error('description')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                    </label>
                    <div class="form-actions full" style="display:flex;justify-content:flex-end;gap:10px;margin-top:10px;">
                        <button class="btn ghost" type="button" onclick="closeCreateModal()">Batal</button>
                        <button id="btnNextStep" class="btn primary" type="button" onclick="goToStep(2)">Lanjut ke pertanyaan →</button>
                    </div>
                </div>

                <!-- Step 2: 3 Initial Questions -->
                <div id="wizardStep2" class="initial-questions" style="display:none;">
                    <div class="wizard-copy">
                        <strong>Tambahkan 3 pertanyaan awal</strong>
                        <p>Pertanyaan dapat diubah kembali setelah area tersimpan.</p>
                    </div>

                    @for($i = 0; $i < 3; $i++)
                        <label>
                            <span>Pertanyaan {{ $i + 1 }} *</span>
                            <textarea id="questionInput_{{ $i }}" name="questions[{{ $i }}]" placeholder="Tulis pertanyaan inspeksi {{ $i + 1 }}" required>{{ old("questions.$i") }}</textarea>
                            @error("questions.$i")<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                        </label>
                    @endfor
                    @error('questions')<small class="field-error" style="color:#b42318;font-size:11px;display:block;">{{ $message }}</small>@enderror

                    <div class="form-actions" style="display:flex;justify-content:space-between;margin-top:16px;">
                        <button class="btn ghost" type="button" onclick="goToStep(1)">← Kembali</button>
                        <button id="btnSubmitArea" class="btn primary" type="submit">Simpan area</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Modal Hapus Area Kerja -->
<div id="deleteAreaModal" class="modal-layer" role="presentation" style="display:none;" onclick="if(event.target === this) closeDeleteModal()">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <header>
            <h2 id="delete-modal-title">Hapus area kerja?</h2>
            <button type="button" aria-label="Tutup" onclick="closeDeleteModal()">✕</button>
        </header>
        <div class="modal-body">
            <div class="confirm-dialog-copy">
                <div class="confirm-dialog-icon" aria-hidden="true">!</div>
                <div>
                    <p>Anda akan menghapus area kerja berikut:</p>
                    <strong id="deleteAreaName" style="margin-bottom:8px;"></strong>
                    <small>Data akan dihapus dari daftar aktif, tetapi riwayatnya tetap tersimpan.</small>
                </div>
            </div>
            <form id="deleteAreaForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="confirm-dialog-actions">
                    <button class="btn ghost" type="button" onclick="closeDeleteModal()">Batal</button>
                    <button class="btn danger" type="submit">Hapus</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endpush

@push('scripts')
<script>
    const createModal = document.getElementById('createAreaModal');
    const deleteModal = document.getElementById('deleteAreaModal');
    const step1El = document.getElementById('wizardStep1');
    const step2El = document.getElementById('wizardStep2');
    const step1Ind = document.getElementById('step1Indicator');
    const step2Ind = document.getElementById('step2Indicator');
    const createNameInput = document.getElementById('createName');

    function openCreateModal() {
        createModal.style.display = 'flex';
        goToStep(1);
    }

    function closeCreateModal() {
        createModal.style.display = 'none';
        goToStep(1);
    }

    function goToStep(step) {
        if (step === 2) {
            if (!createNameInput.value.trim()) {
                createNameInput.focus();
                createNameInput.reportValidity();
                return;
            }
            step1El.style.display = 'none';
            step2El.style.display = 'grid';
            step1Ind.className = 'done';
            step2Ind.className = 'active';
        } else {
            step2El.style.display = 'none';
            step1El.style.display = 'grid';
            step1Ind.className = 'active';
            step2Ind.className = '';
        }
    }

    function openDeleteModal(areaId, areaName) {
        document.getElementById('deleteAreaName').textContent = areaName;
        document.getElementById('deleteAreaForm').action = `{{ url('/admin/work-areas') }}/${areaId}`;
        deleteModal.style.display = 'flex';
    }

    function closeDeleteModal() {
        deleteModal.style.display = 'none';
    }

    // Auto reopen wizard on validation error
    @if($errors->has('questions') || $errors->has('questions.*'))
        openCreateModal();
        goToStep(2);
    @elseif($errors->has('name') || $errors->has('description'))
        openCreateModal();
        goToStep(1);
    @endif
</script>
@endpush
