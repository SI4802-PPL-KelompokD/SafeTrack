@extends('layouts.safetrack', ['title' => 'Area dan Pertanyaan', 'pageTitle' => 'Data Master'])
@section('content')
@vite(['resources/css/work-areas.css', 'resources/js/work-areas.js'])
<div class="work-area-content">
@yield('area-content')
</div>
@endsection
@push('modals')
<dialog id="admin-confirm" class="wa-dialog wa-confirm" aria-labelledby="confirm-title">
<header><h2 id="confirm-title">Konfirmasi simpan perubahan</h2><button type="button" data-close-dialog aria-label="Tutup">×</button></header>
<form id="admin-confirm-form">
<div class="wa-dialog-body">
<label>Nama petugas Admin <span>*</span><input name="admin_name" required minlength="3" maxlength="100" autocomplete="name" placeholder="Ketik nama petugas Admin"></label>
<label>Alasan perubahan <span>*</span><textarea name="action_reason" required minlength="10" maxlength="1000" rows="3" placeholder="Jelaskan alasan perubahan, minimal 10 karakter"></textarea></label>
<p id="identity-error" class="field-error" role="alert"></p>
</div>
<footer><button type="button" class="btn ghost" data-close-dialog>Batal</button><button class="btn primary" id="confirm-submit">Simpan perubahan</button></footer>
</form></dialog>

@endpush
