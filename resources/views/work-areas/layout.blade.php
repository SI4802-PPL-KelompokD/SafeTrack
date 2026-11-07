<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0f2a4a"><link rel="manifest" href="/manifest.webmanifest"><link rel="icon" href="/icons/safetrack-192.svg">
<title>@yield('title', 'Area dan Pertanyaan') · SafeTrack</title>
@vite(['resources/css/app.css', 'resources/css/work-areas.css', 'resources/js/work-areas.js'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@include('shared.admin-styles')
</head>
<body><div class="app-shell">
@include('shared.admin-shell')
<div class="content-container work-area-content">
@if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert error" role="alert">{{ session('error') }}</div>@endif
@if($errors->any())
<div class="alert error" role="alert"><strong>Perubahan belum tersimpan.</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
@endif
@yield('content')
@include('shared.admin-footer')
</div></main></div>
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
</body></html>
