@extends('layouts.safetrack')

@section('title', 'Detail Laporan')

@section('content')
    <a class="back-link" href="{{ route($isOwner ? 'hazards.mine' : 'hazards.index') }}">&larr; Kembali ke daftar</a>

    <div class="page-head">
        <div>
            <div class="kicker">{{ $report->code() }}</div>
            <h1>{{ $report->title }}</h1>
            <p>
                <span class="badge badge-{{ $report->statusBadge() }}">{{ $report->statusLabel() }}</span>
                <span class="badge badge-{{ $report->riskBadge() }}">Risiko {{ $report->riskLabel() }}</span>
                @if ($report->trashed())
                    <span class="badge badge-gray">Diarsipkan</span>
                @endif
            </p>
        </div>
    </div>

    @if ($report->status === \App\Models\HazardReport::STATUS_REJECTED)
        <div class="alert-box error">
            <strong>Laporan ditolak.</strong>
            {{ $report->verification_notes ?: 'Tidak ada alasan yang dicantumkan.' }}
        </div>
    @endif

    <section class="panel">
        <h2>Informasi laporan</h2>
        <dl class="detail-grid">
            <div class="detail-item"><dt>Pelapor</dt><dd>{{ $report->reporter?->name ?? '—' }}</dd></div>
            <div class="detail-item"><dt>Tanggal pelaporan</dt><dd>{{ \App\Support\IdFormat::dateTime($report->reported_at) }}</dd></div>
            <div class="detail-item"><dt>Lokasi</dt><dd>{{ $report->location }}</dd></div>
            <div class="detail-item"><dt>Area kerja</dt><dd>{{ $report->workArea?->name ?? '—' }}</dd></div>
            <div class="detail-item"><dt>Kategori</dt><dd>{{ $report->category?->name ?? '—' }}</dd></div>
            <div class="detail-item"><dt>Departemen pelapor</dt><dd>{{ $report->reporterDepartment?->name ?? '—' }}</dd></div>
            <div class="detail-item"><dt>PIC perbaikan</dt><dd>{{ $report->repairDepartment?->name ?? 'Belum ditetapkan' }}</dd></div>
            <div class="detail-item"><dt>Ditangani oleh</dt><dd>{{ $report->repairPic?->name ?? 'Belum diambil' }}</dd></div>
            @if ($isAdmin)
                <div class="detail-item"><dt>Admin penanggung jawab</dt><dd>{{ $report->assignedAdmin?->name ?? '—' }}</dd></div>
                <div class="detail-item">
                    <dt>Diverifikasi oleh</dt>
                    <dd>{{ $report->verifier?->name ?? '—' }}@if ($report->verified_at) <span class="muted">· {{ \App\Support\IdFormat::dateTime($report->verified_at) }}</span>@endif</dd>
                </div>
            @endif
            @if ($report->closed_at)
                <div class="detail-item"><dt>Ditutup pada</dt><dd>{{ \App\Support\IdFormat::dateTime($report->closed_at) }}</dd></div>
            @endif
        </dl>
    </section>

    <section class="panel">
        <h2>Deskripsi temuan</h2>
        <p class="prose">{{ $report->description }}</p>
        <h2 style="margin-top:18px;">Tindakan awal</h2>
        <p class="prose">{{ $report->immediate_action ?: 'Tidak ada tindakan awal yang dicatat.' }}</p>
    </section>

    <section class="panel">
        <h2>Foto temuan</h2>
        @if ($report->photos->isEmpty())
            <p class="muted">Belum ada foto.</p>
        @else
            <div class="photo-grid">
                @foreach ($report->photos as $photo)
                    <a href="{{ route('hazards.photos.show', [$report, $photo]) }}" target="_blank" rel="noopener">
                        <img src="{{ route('hazards.photos.show', [$report, $photo]) }}" alt="Foto temuan {{ $loop->iteration }}" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($report->resolutions->isNotEmpty())
        <section class="panel">
            <h2>Bukti perbaikan</h2>
            @foreach ($report->resolutions as $resolution)
                <div style="{{ ! $loop->first ? 'margin-top:18px;padding-top:18px;border-top:1px solid #e2e8f0;' : '' }}">
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px;">
                        <span class="badge badge-{{ $resolution->statusBadge() }}">{{ $resolution->statusLabel() }}</span>
                        <span class="muted">
                            Dikirim {{ $resolution->submitter?->name ?? '—' }} · {{ \App\Support\IdFormat::dateTime($resolution->submitted_at) }}
                        </span>
                    </div>
                    <p class="prose">{{ $resolution->description }}</p>
                    @if ($resolution->review_notes)
                        <p class="muted" style="margin-top:8px;">
                            Catatan HSE{{ $resolution->reviewer ? ' ('.$resolution->reviewer->name.')' : '' }}: {{ $resolution->review_notes }}
                        </p>
                    @endif
                    @if ($resolution->photos->isNotEmpty())
                        <div class="photo-grid" style="margin-top:10px;">
                            @foreach ($resolution->photos as $photo)
                                <a href="{{ route('hazards.resolution-photos.show', [$report, $photo]) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('hazards.resolution-photos.show', [$report, $photo]) }}" alt="Foto bukti perbaikan {{ $loop->iteration }}" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    <section class="panel">
        <h2>Riwayat status</h2>
        @if ($report->histories->isEmpty())
            <p class="muted">Belum ada riwayat.</p>
        @else
            <ul class="timeline">
                @foreach ($report->histories as $history)
                    <li>
                        <strong>{{ $history->fromLabel() }} &rarr; {{ $history->toLabel() }}</strong>
                        @if ($history->reason)
                            <div>{{ $history->reason }}</div>
                        @endif
                        <div class="when">{{ $history->actor?->name ?? '—' }} · {{ \App\Support\IdFormat::dateTime($history->created_at) }}</div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Aksi (verifikasi, ambil penanganan, kirim bukti, tutup, arsipkan) bukan bagian PBI-13.
         Tambahkan tombolnya di sini dengan @can(...) ketika PBI terkait dikerjakan. --}}
@endsection
