@extends('layouts.safetrack', ['title' => 'Ubah ' . $workArea->name, 'pageTitle' => 'Ubah Area Kerja'])

@section('content')
<header class="page-head">
    <div>
        <span class="eyebrow">DATA MASTER</span>
        <h1>Ubah area kerja</h1>
        <p>Perbarui informasi area dan pertanyaan inspeksi.</p>
    </div>
    <div class="actions">
        <a class="btn" href="{{ route('admin.work-areas.index') }}">← Kembali</a>
        <button class="btn primary" type="button" onclick="openCreateQuestionModal()">+ Tambah pertanyaan</button>
    </div>
</header>

<!-- Area Information Section -->
<section class="panel area-editor" style="margin-bottom:24px;">
    <div class="panel-head">
        <div>
            <h2>Informasi area</h2>
            <p>Ubah nama dan deskripsi area kerja.</p>
        </div>
        @if(session('success') && !session('question_action'))
            <span class="save-indicator">Tersimpan</span>
        @endif
    </div>

    <form class="area-editor-form" method="POST" action="{{ route('admin.work-areas.update', $workArea) }}">
        @csrf
        @method('PUT')

        <label>
            Nama area kerja *
            <input name="name" value="{{ old('name', $workArea->name) }}" required>
            @error('name')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
        </label>

        <label>
            Deskripsi
            <textarea name="description" placeholder="Jelaskan lokasi atau fungsi area kerja.">{{ old('description', $workArea->description) }}</textarea>
            @error('description')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
        </label>

        <div class="area-editor-actions">
            <button class="btn primary" type="submit">Simpan perubahan</button>
        </div>
    </form>
</section>

<!-- Inspection Questions Section -->
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Pertanyaan inspeksi</h2>
            <p>Atur pertanyaan yang digunakan pada area {{ $workArea->name }}.</p>
        </div>
        <span class="badge">{{ $questions->total() }} pertanyaan</span>
    </div>

    @forelse($questions as $item)
        <article class="question-row">
            <div>
                <span class="order">{{ $item->sort_order }}</span>
                <strong>{{ $item->question }}</strong>
                <small>Diperbarui {{ $item->updated_at ? $item->updated_at->format('d M Y') : '-' }}</small>
            </div>
            <div class="question-actions">
                <span class="badge {{ $item->is_active ? 'green' : 'gray' }}">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <button class="action-btn action-edit" type="button" onclick="openEditQuestionModal({{ $item->id }}, @js($item->question), {{ $item->sort_order }}, {{ $item->is_active ? 'true' : 'false' }})">Ubah</button>
                <form method="POST" action="{{ route('admin.work-areas.questions.toggle-status', [$workArea, $item]) }}" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button class="action-btn {{ $item->is_active ? 'action-disable' : 'action-enable' }}" type="submit">
                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <button class="action-btn action-delete" type="button" onclick="openDeleteQuestionModal({{ $item->id }}, @js($item->question))">Hapus</button>
            </div>
        </article>
    @empty
        <div class="empty">Belum ada pertanyaan inspeksi untuk area ini.</div>
    @endforelse

    @if($questions->hasPages())
        <div style="padding: 16px 20px;">
            {{ $questions->links() }}
        </div>
    @endif
</section>
@endsection

@push('modals')
<!-- Modal Tambah / Ubah Pertanyaan -->
<div id="questionModal" class="modal-layer" role="presentation" style="display:none;" onclick="if(event.target === this) closeQuestionModal()">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="question-modal-title">
        <header>
            <h2 id="question-modal-title">Tambah pertanyaan</h2>
            <button type="button" aria-label="Tutup" onclick="closeQuestionModal()">✕</button>
        </header>
        <div class="modal-body">
            <form id="questionForm" method="POST" action="{{ route('admin.work-areas.questions.store', $workArea) }}" class="form-grid">
                @csrf
                <div id="questionMethodField"></div>

                <label class="full" style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                    Teks pertanyaan *
                    <textarea id="modalQuestionText" name="question" required style="width:100%;min-height:92px;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;resize:vertical;color:#334155;">{{ old('question') }}</textarea>
                    @error('question')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                </label>

                <label style="display:flex;flex-direction:column;gap:7px;color:#475569;font-size:12px;font-weight:600;">
                    Urutan *
                    <input id="modalQuestionOrder" type="number" min="1" name="sort_order" value="{{ old('sort_order', 1) }}" required style="width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;color:#334155;">
                    @error('sort_order')<small class="field-error" style="color:#b42318;font-size:11px;">{{ $message }}</small>@enderror
                </label>

                <label class="check" style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;margin-top:24px;">
                    <input id="modalQuestionActive" type="checkbox" name="is_active" value="1" checked>
                    Pertanyaan aktif
                </label>

                <div class="form-actions full" style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                    <button class="btn ghost" type="button" onclick="closeQuestionModal()">Batal</button>
                    <button class="btn primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- Modal Hapus Pertanyaan -->
<div id="deleteQuestionModal" class="modal-layer" role="presentation" style="display:none;" onclick="if(event.target === this) closeDeleteQuestionModal()">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-q-modal-title">
        <header>
            <h2 id="delete-q-modal-title">Hapus pertanyaan inspeksi?</h2>
            <button type="button" aria-label="Tutup" onclick="closeDeleteQuestionModal()">✕</button>
        </header>
        <div class="modal-body">
            <div class="confirm-dialog-copy">
                <div class="confirm-dialog-icon" aria-hidden="true">!</div>
                <div>
                    <p>Anda akan menghapus pertanyaan berikut:</p>
                    <strong id="deleteQuestionItemName" style="margin-bottom:8px;"></strong>
                    <small>Data akan dihapus dari daftar aktif, tetapi riwayatnya tetap tersimpan.</small>
                </div>
            </div>
            <form id="deleteQuestionForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="confirm-dialog-actions">
                    <button class="btn ghost" type="button" onclick="closeDeleteQuestionModal()">Batal</button>
                    <button class="btn danger" type="submit">Hapus</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endpush

@push('scripts')
<script>
    const questionModal = document.getElementById('questionModal');
    const questionForm = document.getElementById('questionForm');
    const questionTitle = document.getElementById('question-modal-title');
    const questionMethod = document.getElementById('questionMethodField');
    const questionText = document.getElementById('modalQuestionText');
    const questionOrder = document.getElementById('modalQuestionOrder');
    const questionActive = document.getElementById('modalQuestionActive');
    const deleteQModal = document.getElementById('deleteQuestionModal');

    function openCreateQuestionModal() {
        questionTitle.textContent = 'Tambah pertanyaan';
        questionForm.action = `{{ route('admin.work-areas.questions.store', $workArea) }}`;
        questionMethod.innerHTML = '';
        questionText.value = '';
        questionOrder.value = '{{ $questions->total() + 1 }}';
        questionActive.checked = true;
        questionModal.style.display = 'flex';
    }

    function openEditQuestionModal(id, text, sortOrder, isActive) {
        questionTitle.textContent = 'Ubah pertanyaan';
        questionForm.action = `{{ url('/admin/work-areas/' . $workArea->id . '/questions') }}/${id}`;
        questionMethod.innerHTML = '@method("PUT")';
        questionText.value = text;
        questionOrder.value = sortOrder;
        questionActive.checked = Boolean(isActive);
        questionModal.style.display = 'flex';
    }

    function closeQuestionModal() {
        questionModal.style.display = 'none';
    }

    function openDeleteQuestionModal(id, text) {
        document.getElementById('deleteQuestionItemName').textContent = text;
        document.getElementById('deleteQuestionForm').action = `{{ url('/admin/work-areas/' . $workArea->id . '/questions') }}/${id}`;
        deleteQModal.style.display = 'flex';
    }

    function closeDeleteQuestionModal() {
        deleteQModal.style.display = 'none';
    }

    // Auto reopen question modal on error
    @if($errors->has('question') || $errors->has('sort_order'))
        questionModal.style.display = 'flex';
    @endif
</script>
@endpush
