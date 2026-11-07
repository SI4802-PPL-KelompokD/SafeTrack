@extends('layouts.safetrack')

@section('title', 'Akses ditolak')
@section('breadcrumb', 'Akses Ditolak')

@section('content')
    <div class="flex min-h-[calc(100dvh-12rem)] items-center justify-center py-8">
        <section class="panel mx-auto w-full max-w-xl rounded-3xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm sm:px-12" aria-labelledby="denied-title">
            <div class="mx-auto mb-6 flex size-20 items-center justify-center rounded-full bg-[#fdf1d8] text-[#0f2a4a]">
                <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <rect x="5" y="10" width="14" height="11" rx="2" />
                    <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2" />
                </svg>
            </div>
            <p class="mb-3 text-xs font-bold tracking-[0.25em] text-amber-600">ERROR 403</p>
            <h1 id="denied-title" class="mb-4 text-3xl font-bold text-[#0f2a4a]">Akses ditolak</h1>
            @auth
                <p class="text-sm leading-7 text-slate-500">Halaman <strong class="font-semibold text-slate-700">{{ $feature ?? 'ini' }}</strong> hanya dapat diakses oleh Admin/HSE. Akun Anda terdaftar sebagai <strong class="font-semibold text-slate-700">{{ auth()->user()->roleLabel() }}</strong>, sehingga tidak memiliki hak akses ke fitur ini.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a class="btn btn-primary" href="{{ route('dashboard') }}">Kembali ke Ringkasan</a>
                    <a class="btn btn-ghost" href="{{ route('hazards.mine') }}">Lihat Laporan Saya</a>
                </div>
            @else
                <p class="text-sm leading-7 text-slate-500">Anda tidak memiliki hak akses ke halaman ini. Silakan masuk untuk melanjutkan.</p>
                <a class="btn btn-primary mt-8" href="{{ route('login') }}">Masuk ke SafeTrack</a>
            @endauth
            <p class="mt-8 border-t border-slate-100 pt-6 text-xs leading-6 text-slate-400">Jika Anda merasa membutuhkan akses ini, hubungi Admin/HSE perusahaan Anda.</p>
        </section>
    </div>
@endsection
