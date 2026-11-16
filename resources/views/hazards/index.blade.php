@extends('layouts.safetrack')

@php
    $isMine = $mode === 'mine';
    $pageTitle = $isMine ? 'Laporan Saya' : 'Laporan Bahaya';
    $formAction = $isMine ? route('hazards.mine') : route('hazards.index');
@endphp

@section('title', $pageTitle)

@section('content')
    <div class="page-head">
        <div>
            <div class="kicker">{{ $isMine ? 'Pelaporan Saya' : 'Pelaporan & Tindak Lanjut' }}</div>
            <h1>{{ $pageTitle }}</h1>
            <p>
                @if ($isMine)
                    Semua laporan bahaya yang Anda kirim beserta statusnya, termasuk yang masih menunggu verifikasi atau ditolak.
                @elseif ($isAdmin)
                    Verifikasi laporan dan bukti perbaikan sebelum menutup temuan.
                @else
                    Semua laporan terverifikasi dapat dilihat dan ditangani bersama.
                @endif
            </p>
        </div>
    </div>

    <section class="panel">
        @if (! $isMine && $isAdmin)
            <div class="tabs">
                <a class="tab {{ ! $archive ? 'active' : '' }}" href="{{ route('hazards.index') }}">Laporan aktif</a>
                <a class="tab {{ $archive ? 'active' : '' }}" href="{{ route('hazards.index', ['archive' => 1]) }}">Arsip</a>
            </div>
        @endif

        <form method="GET" action="{{ $formAction }}" class="toolbar">
            @if ($archive)
                <input type="hidden" name="archive" value="1">
            @endif
            <div class="field grow">
                <label for="q">Cari</label>
                <input class="input" id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari laporan...">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @unless ($isMine)
                <div class="field">
                    <label for="department_id">Departemen</label>
                    <select class="select" id="department_id" name="department_id">
                        <option value="">Semua departemen</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless
            <div class="field">
                <label for="from">Dari tanggal</label>
                <input class="input" id="from" type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="field">
                <label for="to">Sampai tanggal</label>
                <input class="input" id="to" type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-primary" type="submit">Terapkan</button>
                <a class="btn btn-ghost" href="{{ $archive ? route('hazards.index', ['archive' => 1]) : $formAction }}">Reset</a>
            </div>
        </form>

        @if ($reports->isEmpty())
            <div class="empty">
                @if ($isMine)
                    <strong>Anda belum membuat laporan</strong>
                    Laporan bahaya yang Anda kirim akan tampil di sini.
                @else
                    <strong>Belum ada laporan</strong>
                    Laporan Hazard Report akan tampil di sini.
                @endif
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Laporan &amp; lokasi</th>
                            @unless ($isMine)
                                <th>Pelapor</th>
                            @endunless
                            <th>Risiko</th>
                            <th>Penanganan</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>
                                    <a class="cell-title" href="{{ route('hazards.show', $report) }}">{{ $report->title }}</a>
                                    <div class="muted">{{ $report->code() }} · {{ $report->location }}</div>
                                </td>
                                @unless ($isMine)
                                    <td>
                                        {{ $report->reporter?->name ?? '—' }}
                                        <div class="muted">{{ $report->reporterDepartment?->name ?? '—' }}</div>
                                    </td>
                                @endunless
                                <td><span class="badge badge-{{ $report->riskBadge() }}">{{ $report->riskLabel() }}</span></td>
                                <td>{{ $report->repairPic?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $report->statusBadge() }}">{{ $report->statusLabel() }}</span>
                                    @if ($report->status === \App\Models\HazardReport::STATUS_REJECTED && $report->verification_notes)
                                        <div class="muted" style="margin-top:4px;">Alasan: {{ \Illuminate\Support\Str::limit($report->verification_notes, 80) }}</div>
                                    @endif
                                </td>
                                <td>{{ \App\Support\IdFormat::date($report->reported_at) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="table-foot">
            <span>
                {{ $reports->total() }} laporan
                @if ($isMine)
                    · hanya laporan Anda
                @elseif ($isAdmin)
                    · {{ $archive ? 'arsip' : 'semua laporan aktif' }}
                @else
                    · laporan Anda dan laporan terverifikasi
                @endif
            </span>
            @include('partials.pager', ['paginator' => $reports])
        </div>
    </section>
@endsection
