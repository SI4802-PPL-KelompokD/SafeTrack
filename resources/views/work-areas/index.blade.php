@extends('work-areas.layout')
@section('title', 'Kelola Area Kerja')
@section('content')
<div class="page-header-row"><div class="page-header-text"><div class="page-category-tag">Administrasi</div><h1 class="page-main-title">Data master</h1><p class="page-desc-text">Satu referensi data untuk aktivitas keselamatan yang konsisten.</p></div><button type="button" class="btn-add-primary" data-open-dialog="create-area">+ Tambah area</button></div>
@include('shared.master-tabs', ['activeMasterTab' => 'areas'])
<section class="panel">
<form class="wa-search" method="GET" action="{{ route('admin.work-areas.index') }}"><label><span aria-hidden="true">⌕</span><input name="search" value="{{ request('search') }}" placeholder="Cari nama area..." aria-label="Cari nama area"></label><button class="btn">Cari</button><a class="btn ghost" href="{{ route('admin.work-areas.index') }}">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Area kerja</th><th>Pertanyaan</th><th>Diperbarui</th><th class="wa-action-heading">Aksi</th></tr></thead><tbody>
@forelse($workAreas as $area)
<tr data-area-id="{{ $area->id }}" data-active="{{ (int) $area->is_active }}"><td><strong>{{ $area->name }}</strong></td><td>{{ $area->questions_count }}</td><td>{{ $area->updated_at?->translatedFormat('d M Y') }}</td><td><div class="wa-actions"><a class="wa-edit" href="{{ route('admin.work-areas.show', $area) }}">Ubah</a>
<form method="POST" action="{{ route('admin.work-areas.toggle-status', $area) }}" data-admin-action data-confirm-title="{{ $area->is_active ? 'Nonaktifkan' : 'Aktifkan' }} area kerja?" data-confirm-label="{{ $area->is_active ? 'Nonaktifkan' : 'Aktifkan' }} area kerja">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $area->is_active ? 0 : 1 }}"><button class="wa-switch {{ $area->is_active ? 'is-on' : '' }}" type="submit" role="switch" aria-checked="{{ $area->is_active ? 'true' : 'false' }}" aria-label="{{ $area->is_active ? 'Nonaktifkan' : 'Aktifkan' }} area {{ $area->name }}"><span aria-hidden="true">{{ $area->is_active ? 'ON' : 'OFF' }}</span><i aria-hidden="true"></i></button></form>
</div></td></tr>
@empty<tr><td colspan="4" class="empty">Tidak ada area yang sesuai. Gunakan pencarian lain atau tambahkan area.</td></tr>@endforelse
</tbody></table></div><div class="wa-pagination">{{ $workAreas->links('work-areas.pagination') }}</div></section>
<dialog id="create-area" class="wa-dialog" aria-labelledby="create-area-title" @if($errors->any() && old('_form') === 'create') data-reopen @endif>
<header><h2 id="create-area-title">Tambah area kerja</h2><button type="button" data-close-dialog aria-label="Tutup">×</button></header>
<form method="POST" action="{{ route('admin.work-areas.store') }}" id="create-area-form" data-admin-action data-wizard data-confirm-title="Konfirmasi tambah area kerja" data-confirm-label="Simpan area">@csrf<input type="hidden" name="_form" value="create">
<div class="wa-dialog-body"><ol class="wa-steps" aria-label="Langkah tambah area"><li data-step-indicator="1" class="current">1 <span>Informasi area</span></li><li data-step-indicator="2">2 <span>Pertanyaan awal</span></li></ol>
<div data-wizard-step="1"><label>Nama area kerja <span>*</span><input name="name" required maxlength="255" value="{{ old('name') }}" placeholder="Contoh: Workshop Utama">@error('name')<small class="field-error">{{ $message }}</small>@enderror</label><label>Deskripsi<textarea name="description" rows="3" maxlength="2000" placeholder="Jelaskan lokasi atau fungsi area kerja.">{{ old('description') }}</textarea></label></div>
<div data-wizard-step="2" hidden><h3>Tambahkan 3 pertanyaan awal</h3><p class="wa-help">Pertanyaan dapat diubah setelah area tersimpan.</p>@for($i=0;$i<3;$i++)<label>Pertanyaan {{ $i+1 }} <span>*</span><textarea name="questions[{{ $i }}]" required maxlength="2000" rows="2">{{ old('questions.'.$i) }}</textarea>@error('questions.'.$i)<small class="field-error">{{ $message }}</small>@enderror</label>@endfor</div>
<p data-wizard-error class="field-error" role="alert"></p></div>
<footer><button type="button" class="btn ghost" data-wizard-back hidden>← Kembali</button><button type="button" class="btn ghost" data-wizard-cancel data-close-dialog>Batal</button><button type="button" class="btn primary" data-wizard-next>Lanjut ke pertanyaan</button><button type="submit" class="btn primary" data-wizard-save hidden>Simpan area</button></footer>
</form></dialog>
@endsection
