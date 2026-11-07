@extends('layouts.safetrack')

@section('title', 'Ringkasan')

@section('content')

            @if (session('success'))
                <div class="alert-box success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-box error">{{ session('error') }}</div>
            @endif

            <div class="welcome-hero-card">
                <h1>Selamat Datang di SafeTrack, {{ Auth::user()->name }}!</h1>
                <p>Peran Anda saat ini: <strong>{{ Auth::user()->role === 'super_admin' ? 'Super Admin' : (Auth::user()->role === 'admin' ? 'Admin / HSE' : 'Karyawan Operasional') }}</strong>. Bersama mewujudkan tempat kerja yang aman dan berbudaya K3.</p>
            </div>

            <div class="stats-row">
                <div class="card-stat">
                    <div class="card-stat-label">Laporan Bahaya Saya</div>
                    <div class="card-stat-val">{{ $sapSummary?->hazard_count ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">Inspeksi Bulan Ini</div>
                    <div class="card-stat-val">{{ $sapSummary?->inspection_count ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">Poin SAP K3</div>
                    <div class="card-stat-val">{{ $sapSummary?->total_points ?? 0 }}</div>
                </div>
                <div class="card-stat">
                    <div class="card-stat-label">
                        @if (Auth::user()->department?->is_hse)
                            Target K3 (HSE)
                        @else
                            Hak Reward K3
                        @endif
                    </div>
                    <div class="card-stat-val" style="font-size:18px;margin-top:6px;">
                        @if (Auth::user()->department?->is_hse)
                            @if ($sapSummary?->target_met === true)
                                <span style="color:#15803d;font-weight:700;">&#10003; Terpenuhi</span>
                            @elseif ($sapSummary?->target_met === false)
                                <span style="color:#b91c1c;font-weight:700;">Belum Terpenuhi</span>
                            @else
                                <span style="color:#64748b;font-size:14px;font-weight:600;">Belum Ada Aturan</span>
                            @endif
                        @else
                            @if ($sapSummary?->reward_eligible && $sapSummary?->eligibleReward)
                                <span style="color:#b45309;font-weight:700;">🎁 {{ $sapSummary->eligibleReward->name }}</span>
                            @else
                                <span style="color:#64748b;font-size:14px;font-weight:600;">Belum Capai Ambang</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if (Auth::user()->can('submit-inspections'))
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;">Akses Akun Karyawan</h3>
                    <p style="font-size:13.5px;color:#64748b;line-height:1.6;">
                        Sebagai karyawan, Anda dapat membuat laporan temuan bahaya K3, mengikuti inspeksi harian di area kerja, dan membaca materi Safety Talk. Fitur administrasi seperti Kelola Shift Kerja dan Data Master dibatasi untuk peran Admin/HSE.
                    </p>
                </div>
            @else
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:24px;">
                    <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;">Akses Cepat Admin/HSE</h3>
                    <p style="font-size:13.5px;color:#64748b;line-height:1.6;margin-bottom:16px;">
                        Anda memiliki akses penuh untuk mengelola master data shift kerja, verifikasi temuan bahaya, dan audit K3.
                    </p>
                    <a href="{{ route('shifts.index') }}" style="display:inline-block;background:#0f2a4a;color:#ffffff;padding:9px 18px;border-radius:6px;text-decoration:none;font-size:13.5px;font-weight:600;">
                        Buka Kelola Shift Kerja &rarr;
                    </a>
                </div>
            @endif
@endsection
