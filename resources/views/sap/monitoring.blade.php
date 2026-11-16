@extends('layouts.safetrack')

@push('styles')
    <style>
        .sapm-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #64748b; margin-bottom: 6px; }
        .sapm-title { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .sapm-desc { font-size: 14px; color: #64748b; margin-bottom: 24px; }
        .sapm-notice { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 12px 16px; font-size: 13.5px; margin-bottom: 20px; }
        .sapm-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .sapm-stat { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; }
        .sapm-stat-label { font-size: 15px; color: #475569; }
        .sapm-stat-value { font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px; }
        .sapm-stat-hint { font-size: 12px; color: #64748b; margin-top: 4px; }
        .sapm-muted { color: #94a3b8; font-weight: 700; }
        .sapm-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; overflow: hidden; }
        .sapm-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
        .sapm-card-head h2 { font-size: 20px; font-weight: 800; color: #0f172a; }
        .sapm-card-head p { font-size: 13px; color: #64748b; margin-top: 4px; }
        .sapm-period { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #334155; background: #ffffff; }
        .sapm-table-wrap { overflow-x: auto; }
        .sapm-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .sapm-table th { text-align: left; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 13px; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .sapm-table td { padding: 16px 24px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .sapm-table tbody tr:last-child td { border-bottom: 0; }
        .sapm-name { font-weight: 700; color: #0f172a; }
        .sapm-sub { font-size: 12px; color: #64748b; margin-top: 2px; }
        .sapm-empty { padding: 28px 24px; text-align: center; color: #64748b; font-size: 14px; }
        .sapm-link { display: inline-block; margin-bottom: 24px; font-size: 13.5px; font-weight: 600; color: #0f2a4a; text-decoration: none; }
        .sapm-link:hover { text-decoration: underline; }
    </style>
@endpush

@section('content')
    <p class="sapm-eyebrow">{{ $mode === 'admin' ? 'Monitoring Keselamatan' : 'Kinerja Keselamatan' }}</p>
    <h1 class="sapm-title">{{ $heading }}</h1>
    <p class="sapm-desc">{{ $description }}</p>

    @if ($mode === 'admin')
        @if (! $hasSummaryData)
            <div class="sapm-notice">Belum terdapat data pencapaian untuk periode ini. Jalankan Hitung Ulang SAP di halaman rekap agar data terisi.</div>
        @endif

        <a class="sapm-link" href="{{ route('sap.index', ['year' => $period->year, 'month' => $period->month]) }}">Rekap lengkap &amp; hitung ulang SAP &rarr;</a>

        <section class="sapm-card">
            <div class="sapm-card-head">
                <div>
                    <h2>Pencapaian Karyawan HSE</h2>
                    <p>Target default: {{ $defaultHazardTarget }} laporan dan {{ $defaultInspectionTarget }} inspeksi per bulan</p>
                </div>
                <form method="GET" action="{{ route('sap.monitoring') }}">
                    <input type="month" name="period" value="{{ $period->format('Y-m') }}" class="sapm-period" aria-label="Periode" onchange="this.form.submit()">
                </form>
            </div>
            <div class="sapm-table-wrap">
                <table class="sapm-table">
                    <thead>
                        <tr>
                            <th>Karyawan HSE</th>
                            <th>Hazard / target</th>
                            <th>Inspeksi / target</th>
                            <th>Safety Talk</th>
                            <th>Pencapaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hseRows as $row)
                            @php
                                $achievement = $row['achievement'];
                            @endphp
                            <tr>
                                <td>
                                    <div class="sapm-name">{{ $row['user']->name }}</div>
                                    <div class="sapm-sub">{{ $row['user']->department?->name ?? '-' }}</div>
                                </td>
                                <td>{{ $achievement['hazard'] }} / {{ $achievement['hazard_target'] }}</td>
                                <td>{{ $achievement['inspection'] }} / {{ $achievement['inspection_target'] }}</td>
                                <td>{{ $achievement['safety_talk'] }}</td>
                                <td class="sapm-name">{{ $achievement['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="sapm-empty">Belum ada karyawan HSE aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="sapm-card">
            <div class="sapm-card-head">
                <div>
                    <h2>Top 3 kontribusi Karyawan</h2>
                    <p>Karyawan dengan poin SAP tertinggi pada periode ini</p>
                </div>
            </div>
            <div class="sapm-table-wrap">
                <table class="sapm-table">
                    <thead>
                        <tr>
                            <th>Peringkat</th>
                            <th>Karyawan</th>
                            <th>Role</th>
                            <th>Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topContributors as $summary)
                            <tr>
                                <td class="sapm-name">#{{ $loop->iteration }}</td>
                                <td>{{ $summary->user?->name ?? '-' }}</td>
                                <td>{{ $summary->is_hse_snapshot ? 'Karyawan HSE' : 'Karyawan Non-HSE' }}</td>
                                <td class="sapm-name">{{ $summary->total_points }} poin</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="sapm-empty">Belum ada data kontribusi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($mode === 'hse')
        @if (! $hasSummaryData)
            <div class="sapm-notice">Belum terdapat data pencapaian bulan ini.</div>
        @endif

        <div class="sapm-stat-grid">
            <div class="sapm-stat">
                <div class="sapm-stat-label">Laporan bahaya / bulan</div>
                <div class="sapm-stat-value">{{ $achievement['hazard'] }} <span class="sapm-muted">/ {{ $achievement['hazard_target'] }}</span></div>
                <div class="sapm-stat-hint">Sisa target: {{ $achievement['hazard_remaining'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Inspeksi harian / bulan</div>
                <div class="sapm-stat-value">{{ $achievement['inspection'] }} <span class="sapm-muted">/ {{ $achievement['inspection_target'] }}</span></div>
                <div class="sapm-stat-hint">Sisa target: {{ $achievement['inspection_remaining'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Safety Talk / bulan</div>
                <div class="sapm-stat-value">{{ $achievement['safety_talk'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Pencapaian target / bulan</div>
                <div class="sapm-stat-value">{{ $achievement['percentage'] }}%</div>
            </div>
        </div>
    @else
        <div class="sapm-stat-grid">
            <div class="sapm-stat">
                <div class="sapm-stat-label">Laporan bahaya / bulan</div>
                <div class="sapm-stat-value">{{ $contribution['hazard'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Inspeksi harian / bulan</div>
                <div class="sapm-stat-value">{{ $contribution['inspection'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Safety Talk / bulan</div>
                <div class="sapm-stat-value">{{ $contribution['safety_talk'] }}</div>
            </div>
            <div class="sapm-stat">
                <div class="sapm-stat-label">Total kontribusi / bulan</div>
                <div class="sapm-stat-value">{{ $contribution['total'] }}</div>
            </div>
        </div>
    @endif
@endsection