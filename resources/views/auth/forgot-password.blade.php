@extends('layouts.auth')

@section('title', 'Lupa Password · SafeTrack')

@push('styles')
    <style>
        .dev-reset-box {
            background: #fffbeb;
            color: #92400e;
            border: 1px dashed #f59e0b;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .dev-reset-box a {
            display: inline-block;
            margin-top: 6px;
            color: #b45309;
            font-weight: 700;
            word-break: break-all;
        }
    </style>
@endpush

@section('content')
    <span class="onboard-label">LUPA PASSWORD</span>
    <h1>Atur ulang password.</h1>
    <p>Masukkan email yang terdaftar pada akun SafeTrack Anda. Kami akan mengirimkan tautan untuk membuat password baru.</p>

    @if (session('success'))
        <div class="auth-alert success" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <div>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('dev_reset_url'))
        <div class="dev-reset-box" role="status">
            <strong>Mode pengembangan:</strong> email tidak benar-benar dikirim (MAIL_MAILER=log).
            <br>
            <a href="{{ session('dev_reset_url') }}">Buka tautan reset password &rarr;</a>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">Email <span class="required">*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                class="{{ $errors->has('email') ? 'has-error' : '' }}"
                placeholder="nama@example.com"
                value="{{ old('email') }}"
                required
                maxlength="255"
                autocomplete="email"
                autofocus
            >
            @error('email')
                <div class="error-text" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary" type="submit">
            Kirim tautan reset &rarr;
        </button>
    </form>

    <p class="auth-note">Sudah ingat password? <a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
@endsection
