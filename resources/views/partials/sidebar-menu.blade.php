@php
    $navigationIsAdmin = auth()->user()?->can('manage-administration') ?? false;
    $linkClass = $linkClass ?? 'sidebar-link';
    $labelClass = $labelClass ?? 'nav-section-title';
    $listClass = $listClass ?? 'sidebar-menu';
    $activeClass = $activeClass ?? 'active';
    $navigationGroups = [
        'Utama' => [
            ['Ringkasan', 'dashboard', ['dashboard'], 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z'],
            ['Laporan Bahaya', 'hazards.index', ['hazards.*'], 'M12 3 2 21h20L12 3Z M12 9v4 M12 17h.01'],
            ['Inspeksi Harian', 'daily-inspections.index', ['daily-inspections.*'], 'M9 5H5v16h14V5h-4 M9 3h6v4H9z M9 12h6 M9 16h6'],
            ['Safety Talk', null, [], 'M4 4h16v12h-6l-4 4v-4H4V4Z M8 8h8 M8 12h4'],
            ['Pencapaian SAP', $navigationIsAdmin ? 'sap.index' : null, ['sap.*'], 'm12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z'],
            ['Notifikasi', null, [], 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9 M10 21h4'],
        ],
    ];

    if ($navigationIsAdmin) {
        $navigationGroups['Administrasi'] = [
            ['Kelola Karyawan', 'admin.pengguna.index', ['admin.pengguna.*'], 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M22 21v-2a4 4 0 0 0-3-3.9 M16 3.1a4 4 0 0 1 0 7.8'],
            ['Data Master', 'admin.data-master', ['admin.data-master', 'shifts.*', 'admin.departments.*', 'admin.work-areas.*'], 'M20 6c0 2-3.6 3-8 3S4 8 4 6s3.6-3 8-3 8 1 8 3Z M4 6v12c0 2 3.6 3 8 3s8-1 8-3V6 M4 12c0 2 3.6 3 8 3s8-1 8-3'],
            ['Ekspor Laporan', null, [], 'M3 16v5h18v-5 M12 3v13 M7 11l5 5 5-5'],
            ['Jejak Audit', null, [], 'M12 6v6h5 M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    }
@endphp

<!-- ADMINISTRASI mengikuti hak akses akun yang sedang login. -->
@foreach ($navigationGroups as $groupLabel => $navigationItems)
    <p class="{{ $labelClass }}">{{ $groupLabel }}</p>
    <ul class="{{ $listClass }}" style="list-style:none;padding:0;">
        @foreach ($navigationItems as [$label, $routeName, $routePatterns, $iconPath])
            @php
                $isCurrentPage = $routeName && request()->routeIs(...$routePatterns);
                $unavailableReason = $label === 'Pencapaian SAP'
                    ? 'Pencapaian SAP saat ini tersedia untuk admin.'
                    : $label . ' belum tersedia.';
            @endphp
            <li>
                <a @if ($routeName) href="{{ route($routeName) }}" @else aria-disabled="true" title="{{ $unavailableReason }}" @endif
                   class="{{ $linkClass }} {{ $isCurrentPage ? $activeClass : '' }}"
                   @if ($isCurrentPage) aria-current="page" @endif
                   @if (! $routeName) style="cursor:default;opacity:.65;" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
                    </svg>
                    <span>{{ $label }}</span>
                    @if (! $routeName && $label === 'Pencapaian SAP')
                        <small style="margin-left:auto;font-size:9px;line-height:1.3;text-align:right;max-width:55px;">Khusus admin</small>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
@endforeach
