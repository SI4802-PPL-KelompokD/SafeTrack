@extends('layouts.auth')

@section('title', 'Registrasi Karyawan · SafeTrack')

@section('content')
    <span class="onboard-label">REGISTRASI KARYAWAN</span>
    <h1>Buat akun SafeTrack.</h1>
    <p>Registrasi ini khusus akun Karyawan. Akun HSE disediakan oleh sistem.</p>

    {{-- Flash Success Message --}}
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

    @error('system')
        <div class="auth-alert error" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div>
                <span>{{ $message }}</span>
            </div>
        </div>
    @enderror

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        {{-- NIK --}}
        <div class="field">
            <label for="nik">NIK <span class="required">*</span></label>
            <input
                type="text"
                id="nik"
                name="nik"
                class="{{ $errors->has('nik') ? 'has-error' : '' }}"
                placeholder="Contoh: EMP-001"
                value="{{ old('nik') }}"
                required
                maxlength="40"
                autofocus
            >
            @error('nik')
                <div class="error-text" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Username --}}
        <div class="field">
            <label for="username">Username <span class="required">*</span></label>
            <input
                type="text"
                id="username"
                name="username"
                class="{{ $errors->has('username') ? 'has-error' : '' }}"
                placeholder="Username"
                value="{{ old('username') }}"
                required
                minlength="3"
                maxlength="60"
                pattern="[a-zA-Z0-9._-]+"
                autocomplete="username"
            >
            @error('username')
                <div class="error-text" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Email --}}
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
            >
            @error('email')
                <div class="error-text" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field">
            <label for="password">Password <span class="required">*</span></label>
            <input
                type="password"
                id="password"
                name="password"
                class="{{ $errors->has('password') ? 'has-error' : '' }}"
                placeholder="Minimal 8 karakter"
                required
                minlength="8"
                autocomplete="new-password"
            >
            @error('password')
                <div class="error-text" role="alert">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;display:inline-block;vertical-align:-1px;margin-right:3px;">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button class="btn btn-primary" type="submit">
            Buat akun Karyawan &rarr;
        </button>
    </form>

    <p class="auth-note">Sudah terdaftar? <a href="{{ route('login') }}">Masuk ke akun</a></p>
@endsection
