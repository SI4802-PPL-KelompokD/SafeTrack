@extends('layouts.auth')

@section('title', 'Reset Password · SafeTrack')

@section('content')
    <span class="onboard-label">RESET PASSWORD</span>
    <h1>Buat password baru.</h1>
    <p>Gunakan minimal 8 karakter. Setelah berhasil, Anda dapat masuk kembali dengan password baru.</p>

    @error('email')
        <div class="auth-alert error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <span>{{ $message }}</span>
                <br>
                <a href="{{ route('password.request') }}">Minta tautan reset baru</a>
            </div>
        </div>
    @enderror

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                maxlength="255"
                autocomplete="email"
                readonly
            >
        </div>

        <div class="field">
            <label for="password">Password baru <span class="required">*</span></label>
            <input
                type="password"
                id="password"
                name="password"
                class="{{ $errors->has('password') ? 'has-error' : '' }}"
                placeholder="Minimal 8 karakter"
                required
                minlength="8"
                autocomplete="new-password"
                autofocus
            >
            @error('password')
                <div class="error-text" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Konfirmasi password baru <span class="required">*</span></label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Ulangi password baru"
                required
                minlength="8"
                autocomplete="new-password"
            >
        </div>

        <button class="btn btn-primary" type="submit">
            Simpan password baru &rarr;
        </button>
    </form>

    <p class="auth-note"><a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
@endsection
