@php
    $paths = [
        'shield' => '<path d="M12 3 4.5 6v5.3c0 5 7.5 9.7 7.5 9.7s7.5-4.7 7.5-9.7V6L12 3Z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'alert' => '<path d="m10.3 4-8 14a2 2 0 0 0 1.7 3h16a2 2 0 0 0 1.7-3l-8-14a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4m0 3v1"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="m8 13 2 2 5-5"/>',
        'talk' => '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.5 9.3 9.3 0 0 1-4-.9L3 21l1.5-5A8.6 8.6 0 0 1 3 11.5 8.5 8.5 0 0 1 12 3a8.5 8.5 0 0 1 9 8.5Z"/><path d="M8 10h8m-8 4h5"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'users' => '<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6m3 11v-3a6 6 0 0 0-3-5"/>',
        'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 4 16 4 16 0V5M4 12v7c0 4 16 4 16 0v-7"/>',
        'download' => '<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>',
        'history' => '<path d="M3 11a9 9 0 1 1 2.5 7M3 4v7h7m2-5v6l4 2"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'chevron' => '<path d="m9 5 7 7-7 7"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'logout' => '<path d="M9 21H4V3h5m4 5 5 4-5 4m-5-4h12"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'building' => '<path d="M4 21V5l8-2v18m0-14h8v14M2 21h20M7 8h2m-2 4h2m-2 4h2m6-5h2m-2 4h2"/>',
        'flow' => '<rect x="8" y="2" width="8" height="5" rx="1"/><rect x="2" y="17" width="7" height="5" rx="1"/><rect x="15" y="17" width="7" height="5" rx="1"/><path d="M12 7v5M5.5 17v-5h13v5"/>',
    ];
    $icon = fn (string $name) => '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? '') . '</svg>';

    $nav = [
        'UTAMA' => [
            ['Ringkasan', 'dashboard', route('dashboard'), false],
            ['Laporan Bahaya', 'alert', '#', false],
            ['Inspeksi Harian', 'clipboard', '#', false],
            ['Safety Talk', 'talk', '#', false],
            ['Pencapaian SAP', 'target', '#', false],
            ['Notifikasi', 'bell', '#', false],
        ],
        'ADMINISTRASI' => [
            ['Kelola Karyawan', 'users', '#', false],
            ['Data Master', 'database', route('admin.departments.index'), true],
            ['Ekspor Laporan', 'download', '#', false],
            ['Jejak Audit', 'history', '#', false],
        ],
    ];

    $user = Auth::user();
    $userName = $user->name ?? 'Administrator';
    $initials = strtoupper(mb_substr($userName, 0, 1) . (mb_strpos($userName, ' ') !== false ? mb_substr($userName, mb_strpos($userName, ' ') + 1, 1) : mb_substr($userName, 1, 1)));
    $roleLabel = ($user->role ?? '') === 'super_admin' ? 'Super Admin' : 'Admin / HSE';

    $editingId = old('_edit_id');
    $reopen = old('_modal');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Data master SafeTrack - Kelola departemen keselamatan kerja.">
    <title>Data Master · Departemen · SafeTrack</title>
    <style>
        /* ===== Design tokens & base (disalin dari mockup SafeTrack) ===== */
        :root{--primary:#17375e;--primary-dark:#0f2744;--accent-soft:#fff3d4;--accent:#f2a900;--accent-strong:#c97c00;--bg:#f6f8fa;--ink:#202d35;--muted:#7b878c;--border:#e5eaed;--white:#fff;--radius:13px;--shadow:0 3px 12px #262f3b10;font-family:'Segoe UI',Arial,sans-serif;color:var(--ink);font-size:14px;font-synthesis:none}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg)}
        button,input,select,textarea{font:inherit}
        button,a,input,select,textarea{-webkit-tap-highlight-color:transparent}
        button,a{touch-action:manipulation}
        button{cursor:pointer}
        button:disabled{opacity:.5;cursor:not-allowed}
        a{color:var(--primary);text-decoration:none}
        button:focus-visible,a:focus-visible{outline:3px solid #7998be;outline-offset:3px}
        input:focus,select:focus,textarea:focus{outline:2px solid #a3b9d3;outline-offset:1px;border-color:var(--primary)}
        h1,h2,h3,h4,p{margin:0}
        h1{font-size:28px;letter-spacing:-.9px;line-height:1.3;font-weight:650}
        h2{font-size:17px;letter-spacing:-.35px;font-weight:650}
        p{line-height:1.65}
        small{font-size:12px}
        svg.icon{width:20px;height:20px;stroke-width:1.7;vertical-align:middle;flex-shrink:0}
        input,select,textarea{color:var(--ink)}
        input::placeholder,textarea::placeholder{color:#96a1a6}

        /* ===== Sidebar ===== */
        .sidebar{width:246px;position:fixed;inset:0 auto 0 0;background:#fff;border-right:1px solid var(--border);z-index:30;display:flex;flex-direction:column;padding:29px 17px 14px}
        .brand{display:flex;align-items:center;gap:10px;padding:0 15px 28px;color:var(--ink)}
        .brand-symbol{width:38px;height:41px;background:var(--primary);display:grid;place-items:center;color:var(--accent);border-radius:11px}
        .brand-symbol .icon{width:25px;height:25px}
        .brand-name{font-size:23px;font-weight:750;letter-spacing:-1px}
        .brand-name span{color:var(--accent-strong)}
        .brand-sub{font-size:9px;letter-spacing:1.1px;color:var(--muted);margin-top:2px}
        .workspace-chip{border:1px solid var(--border);border-radius:9px;display:flex;align-items:center;gap:10px;margin:0 6px 26px;padding:10px}
        .workspace-logo{background:#edf1f4;width:30px;height:30px;display:grid;place-items:center;border-radius:7px;color:#5f707b}
        .workspace-chip strong{font-size:12px;font-weight:600}
        .workspace-chip small{display:block;font-size:10px;margin-top:3px;color:var(--muted)}
        .nav-scroll{overflow-y:auto;min-height:0}
        .nav-label{font-size:9px;font-weight:700;letter-spacing:1.5px;color:#98a3a8;padding:4px 15px 10px}
        .nav-label:not(:first-child){margin-top:21px}
        .nav-item{border:0;background:none;width:100%;display:flex;align-items:center;gap:12px;text-align:left;color:#697780;padding:11px 14px;margin:2px 0;border-radius:7px;font-size:12px;font-weight:500;text-decoration:none}
        .nav-item .icon{width:18px;height:18px}
        .nav-item:hover{background:#f4f5f7;color:var(--primary)}
        .nav-item.active{background:var(--accent-soft);color:var(--primary-dark);font-weight:650;box-shadow:inset 3px 0 0 var(--accent)}
        .sidebar-foot{margin-top:auto;padding-top:16px}
        .guide-card{background:#f4f6f8;border:1px solid #e2e6ea;border-radius:10px;padding:13px;margin:0 4px 16px}
        .guide-card strong{font-size:12px}
        .guide-card p{font-size:11px;color:var(--muted);margin:7px 0 10px}
        .guide-card a{width:100%;font-size:11px;background:white;border:1px solid #dadee4;border-radius:6px;padding:7px;color:var(--primary);text-align:left;display:flex;justify-content:space-between;align-items:center}
        .guide-card a .icon{width:14px;height:14px}
        .side-user{border-top:1px solid var(--border);padding:17px 7px 2px;display:flex;align-items:center;gap:9px}
        .side-user .user-info{flex:1;min-width:0}
        .side-user strong{font-size:11px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .side-user small{font-size:10px;color:var(--muted);display:block;margin-top:3px}
        .avatar{width:33px;height:33px;background:#dce5ef;border:2px solid #fff;border-radius:50%;color:#435973;display:grid;place-items:center;font-size:11px;font-weight:650;flex-shrink:0}

        /* ===== Topbar & shell ===== */
        .main-shell{margin-left:246px}
        .topbar{height:79px;border-bottom:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:space-between;padding:0 36px;gap:20px}
        .topbar .row{display:flex;align-items:center;gap:12px}
        .crumb{display:flex;align-items:center;gap:12px;font-size:11px;color:#9ba5aa}
        .crumb .icon{width:14px;height:14px}
        .crumb strong{font-weight:500;color:#4b5b65}
        .top-actions{display:flex;align-items:center;gap:14px}
        .top-search{position:relative;margin:0}
        .top-search input{background:#f6f8f9;border:1px solid #eef0f2;border-radius:7px;padding:9px 12px 9px 34px;width:205px;font-size:11px}
        .top-search .icon{position:absolute;left:11px;top:9px;width:15px;height:15px;color:#89979d}
        .icon-btn{border:0;background:transparent;position:relative;display:inline-grid;place-items:center;width:33px;height:33px;border-radius:8px;color:#76868e}
        .icon-btn:hover{background:var(--accent-soft);color:var(--primary)}
        .icon-btn .icon{width:18px;height:18px}
        .mobile-menu{display:none}
        .content{max-width:1600px;margin:0 auto;padding:30px 36px 25px}
        @media(min-width:1600px){.content{padding:37px 46px}.topbar{padding-inline:46px}}

        /* ===== Page head ===== */
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:25px}
        .kicker{font-size:9px;font-weight:600;letter-spacing:1.7px;color:#7b8694;text-transform:uppercase;margin-bottom:6px}
        .page-head p{font-size:12px;color:var(--muted);margin-top:7px}
        .head-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}

        /* ===== Buttons ===== */
        .btn{border:1px solid var(--border);background:white;color:#52626b;border-radius:7px;min-height:36px;padding:8px 13px;display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;font-size:11px;font-weight:600;line-height:1.5;text-decoration:none}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:white;box-shadow:0 2px 3px #132f5115}
        .btn-primary:hover{background:#143053;color:#fff;border-color:#143053}

        /* ===== Data master ===== */
        .master-tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .panel{background:white;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px}
        .master-searchbar{display:flex;gap:12px;padding:18px 22px;align-items:center;margin:0}
        .master-search{width:min(440px,100%);padding:11px 13px;border:1px solid var(--border);border-radius:7px;font-size:12px}
        .table-wrap{overflow-x:auto}
        .data-table{border-collapse:collapse;width:100%;font-size:11px;white-space:nowrap}
        .data-table th{text-align:left;font-weight:500;color:#96a2a9;background:#fafbfc;padding:13px 22px;border-block:1px solid #f0f2f4;font-size:10px}
        .data-table td{padding:15px 22px;border-bottom:1px solid #eff1f3;color:#6d7b83}
        .data-table tbody tr:last-child td{border-bottom:0}
        .data-table strong{color:var(--ink);font-weight:600}
        .master-actions{display:flex;align-items:center;gap:20px;white-space:nowrap}
        .master-actions .text-action{border:0;background:none;color:#17375e;font-weight:700;cursor:pointer;padding:8px 0;font-size:11px}
        .master-actions .text-action.danger{color:#b42318}
        .table-footer{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 22px;border-top:1px solid #eff1f3;font-size:10px;color:#9097a0}
        .table-footer .pager{display:flex;gap:8px}
        .table-footer .btn{font-size:10px;padding:7px 10px;min-height:32px}
        .table-footer .btn.disabled{opacity:.5;pointer-events:none}

        /* ===== Modal (dialog) & form ===== */
        dialog{border:0;border-radius:16px;padding:0;width:min(730px,calc(100% - 32px));max-height:90vh;color:var(--ink);box-shadow:0 25px 100px #13233740}
        dialog::backdrop{background:#162d3b70;backdrop-filter:blur(4px)}
        .modal-head{display:flex;justify-content:space-between;align-items:center;padding:23px 27px;border-bottom:1px solid var(--border);position:sticky;top:0;background:white;z-index:5}
        .modal-head h2{font-size:19px}
        .modal-body{padding:24px 28px 28px}
        .modal-body>p{font-size:12px;margin-bottom:16px}
        .master-form-fields{display:grid;gap:16px}
        .field{display:flex;flex-direction:column;gap:7px;min-width:0}
        .field>span{font-weight:600;font-size:11px;color:#53656c}
        .field>span b{color:#b42318}
        .field input{border:1px solid #dce1e7;background:#fff;border-radius:7px;padding:11px;max-width:100%;width:100%;font-size:12px}
        .field input[readonly]{background:#f4f6f8;color:#6d7b83;cursor:not-allowed}
        .field small,.help{font-size:10px;color:#9097a0;line-height:1.6;font-weight:400}
        .master-error{color:#b42318;font-size:12px;min-height:18px}
        .form-actions{display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--border);padding-top:20px;margin-top:7px}

        /* ===== Toast & footer ===== */
        #toast{position:fixed;bottom:26px;left:50%;width:max-content;transform:translate(-50%,20px);background:#23364e;color:white;padding:13px 23px;border-radius:10px;box-shadow:0 8px 30px #10203325;font-size:11px;opacity:0;pointer-events:none;transition:.2s;z-index:100;max-width:calc(100% - 30px);line-height:1.5}
        #toast.visible{opacity:1;transform:translate(-50%,0)}
        .footer{font-size:9px;color:#a1a7ae;display:flex;justify-content:space-between;margin-top:22px}
        .live-dot{width:5px;height:5px;border-radius:50%;background:var(--accent);display:inline-block;margin-right:5px}

        /* ===== Responsive ===== */
        @media(max-width:1200px){.sidebar{width:219px;padding-inline:13px}.main-shell{margin-left:219px}.content{padding:25px}.topbar{padding:0 25px}.top-search{display:none}.data-table th,.data-table td{padding-inline:16px}}
        @media(max-width:760px){
            .sidebar{transform:translateX(-100%);transition:transform .2s;box-shadow:10px 0 50px #16233320;width:240px}
            .sidebar.mobile-open{transform:translateX(0)}
            .main-shell{margin-left:0}
            .topbar{height:65px;padding:0 18px}
            .mobile-menu{display:grid}
            .crumb>span,.crumb>svg{display:none}
            .content{padding:23px 17px}
            .page-head{display:block;margin-bottom:20px}
            .head-actions{margin-top:16px}
            .page-head h1{font-size:25px}
            .master-searchbar{flex-wrap:wrap}
            .modal-head{padding:18px 20px}
            .modal-head h2{font-size:17px}
            .modal-body{padding:20px}
            .footer{font-size:8px;line-height:1.7;gap:15px}
        }
        @media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
    </style>
</head>
<body>
<aside class="sidebar" id="sidebar">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="SafeTrack Ringkasan">
        <div class="brand-symbol">{!! $icon('shield') !!}</div>
        <div>
            <div class="brand-name">safe<span>Track</span></div>
            <div class="brand-sub">SAFETY MANAGEMENT</div>
        </div>
    </a>
    <div class="workspace-chip">
        <div class="workspace-logo">{!! $icon('building') !!}</div>
        <div><strong>PT Sentra Industri</strong><small>Workspace demonstrasi</small></div>
    </div>
    <nav class="nav-scroll" aria-label="Navigasi utama">
        @foreach ($nav as $label => $items)
            <div class="nav-label">{{ $label }}</div>
            @foreach ($items as [$text, $ic, $href, $active])
                <a href="{{ $href }}" class="nav-item {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>{!! $icon($ic) !!}{{ $text }}</a>
            @endforeach
        @endforeach
    </nav>
    <div class="sidebar-foot">
        <div class="guide-card">
            <strong>{!! $icon('flow') !!} Jelajahi alur aplikasi</strong>
            <p>Kenali setiap langkah, dari temuan hingga penyelesaian.</p>
            <a href="#">Lihat panduan alur {!! $icon('arrow') !!}</a>
        </div>
        <div class="side-user">
            <div class="avatar">{{ $initials }}</div>
            <div class="user-info"><strong>{{ $userName }}</strong><small>{{ $roleLabel }}</small></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="icon-btn" aria-label="Keluar dari akun">{!! $icon('logout') !!}</button>
            </form>
        </div>
    </div>
</aside>

<div class="main-shell">
    <header class="topbar">
        <div class="row">
            <button type="button" class="icon-btn mobile-menu" id="menuBtn" aria-label="Buka menu">{!! $icon('menu') !!}</button>
            <div class="crumb"><span>Workspace</span>{!! $icon('chevron') !!}<strong>Data Master</strong></div>
        </div>
        <div class="top-actions">
            <form class="top-search" onsubmit="return false">
                {!! $icon('search') !!}
                <input name="q" aria-label="Cari laporan bahaya" placeholder="Cari laporan bahaya...">
            </form>
            <button type="button" class="icon-btn" aria-label="Notifikasi">{!! $icon('bell') !!}</button>
        </div>
    </header>

    <main class="content" id="main-content">
        <div class="page-head">
            <div>
                <div class="kicker">ADMINISTRASI</div>
                <h1>Data master</h1>
                <p>Kelola referensi data keselamatan kerja.</p>
            </div>
            <div class="head-actions">
                <button type="button" class="btn btn-primary" id="btnAdd">+ Tambah departemen</button>
            </div>
        </div>

        <div class="master-tabs">
            <a href="{{ route('admin.departments.index') }}" class="btn btn-primary" aria-current="page">Departemen</a>
            <a href="{{ route('shifts.index') }}" class="btn">Shift kerja</a>
            <a href="{{ route('admin.work-areas.index') }}" class="btn">Area &amp; pertanyaan</a>
        </div>

        <section class="panel">
            <form class="master-searchbar" method="GET" action="{{ route('admin.departments.index') }}">
                <input class="master-search" name="search" value="{{ request('search') }}" placeholder="Cari nama departemen...">
                <button class="btn" type="submit">Cari</button>
                <a class="btn" href="{{ route('admin.departments.index') }}">Reset</a>
            </form>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Nama</th><th>Diperbarui</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $dept)
                            <tr>
                                <td><strong>{{ $dept->name }}</strong></td>
                                <td>{{ optional($dept->updated_at)->locale('id')->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="master-actions">
                                        <button type="button" class="text-action js-edit"
                                            data-name="{{ $dept->name }}"
                                            data-url="{{ route('admin.departments.update', $dept->id) }}"
                                            data-id="{{ $dept->id }}"
                                            data-locked="{{ $dept->is_referenced ? '1' : '0' }}">Ubah</button>
                                        <button type="button" class="text-action danger js-delete"
                                            data-name="{{ $dept->name }}"
                                            data-url="{{ route('admin.departments.destroy', $dept->id) }}"
                                            data-blocked="{{ ($dept->is_referenced || $dept->is_hse) ? '1' : '0' }}">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Tidak ada data yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($departments->total() > 0)
                <div class="table-footer">
                    <span>Menampilkan {{ $departments->firstItem() }}–{{ $departments->lastItem() }} dari {{ $departments->total() }} departemen</span>
                    @if ($departments->hasPages())
                        <div class="pager">
                            <a class="btn {{ $departments->onFirstPage() ? 'disabled' : '' }}" href="{{ $departments->previousPageUrl() ?? '#' }}">Sebelumnya</a>
                            <a class="btn {{ $departments->hasMorePages() ? '' : 'disabled' }}" href="{{ $departments->nextPageUrl() ?? '#' }}">Berikutnya</a>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <footer class="footer">
            <span>© 2026 SafeTrack. Setiap kepedulian berarti.</span>
            <span><i class="live-dot"></i>Sistem aktif · Data tersimpan otomatis</span>
        </footer>
    </main>
</div>

{{-- Modal Tambah --}}
<dialog id="addModal" aria-labelledby="addTitle">
    <div class="modal-head"><h2 id="addTitle">Tambah departemen</h2><button type="button" class="icon-btn" data-close aria-label="Tutup dialog">{!! $icon('close') !!}</button></div>
    <div class="modal-body">
        <form method="POST" action="{{ route('admin.departments.store') }}" class="master-form-fields" novalidate>
            @csrf
            <input type="hidden" name="_modal" value="add">
            <label class="field">
                <span>Nama <b>*</b></span>
                <input name="name" id="addName" required maxlength="90" value="{{ $reopen === 'add' ? old('name') : '' }}">
                <div class="master-error">{{ $reopen === 'add' ? $errors->first('name') : '' }}</div>
            </label>
            <div class="form-actions">
                <button type="button" class="btn" data-close>Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

{{-- Modal Ubah --}}
<dialog id="editModal" aria-labelledby="editTitle">
    <div class="modal-head"><h2 id="editTitle">Ubah departemen</h2><button type="button" class="icon-btn" data-close aria-label="Tutup dialog">{!! $icon('close') !!}</button></div>
    <div class="modal-body">
        <form method="POST" id="editForm" class="master-form-fields" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="_modal" value="edit">
            <input type="hidden" name="_edit_id" id="editId" value="{{ $reopen === 'edit' ? $editingId : '' }}">
            <label class="field">
                <span>Nama <b>*</b></span>
                <input name="name" id="editName" required maxlength="90" value="{{ $reopen === 'edit' ? old('name') : '' }}">
                <small id="editLocked" style="display:none">Nama dikunci karena sudah digunakan pada data lain.</small>
                <div class="master-error">{{ $reopen === 'edit' ? $errors->first('name') : '' }}</div>
            </label>
            <div class="form-actions">
                <button type="button" class="btn" data-close>Batal</button>
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

{{-- Modal Hapus --}}
<dialog id="deleteModal" aria-labelledby="deleteTitle">
    <div class="modal-head"><h2 id="deleteTitle">Hapus departemen?</h2><button type="button" class="icon-btn" data-close aria-label="Tutup dialog">{!! $icon('close') !!}</button></div>
    <div class="modal-body">
        <p><strong id="deleteName"></strong> belum digunakan. Hapus dari referensi aplikasi?</p>
        <form method="POST" id="deleteForm" class="master-form-fields">
            @csrf
            @method('DELETE')
            <div class="form-actions">
                <button type="button" class="btn" data-close>Batal</button>
                <button class="btn btn-primary">Hapus</button>
            </div>
        </form>
    </div>
</dialog>

<div id="toast" role="status" aria-live="polite"></div>

<script>
(function () {
    var toastEl = document.getElementById('toast'), toastTimer;
    function toast(message) {
        toastEl.textContent = message;
        toastEl.classList.add('visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('visible'); }, 4200);
    }

    var addModal = document.getElementById('addModal'),
        editModal = document.getElementById('editModal'),
        deleteModal = document.getElementById('deleteModal'),
        editForm = document.getElementById('editForm'),
        editName = document.getElementById('editName'),
        editLocked = document.getElementById('editLocked');

    function clearErrors(modal) {
        modal.querySelectorAll('.master-error').forEach(function (el) { el.textContent = ''; });
    }

    document.getElementById('btnAdd').addEventListener('click', function () {
        document.getElementById('addName').value = '';
        clearErrors(addModal);
        addModal.showModal();
        document.getElementById('addName').focus();
    });

    document.querySelectorAll('.js-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var locked = btn.dataset.locked === '1';
            editForm.action = btn.dataset.url;
            document.getElementById('editId').value = btn.dataset.id;
            editName.value = btn.dataset.name;
            editName.readOnly = locked;
            editLocked.style.display = locked ? 'block' : 'none';
            clearErrors(editModal);
            editModal.showModal();
            if (!locked) editName.focus();
        });
    });

    document.querySelectorAll('.js-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.dataset.blocked === '1') {
                toast('Tidak dapat dihapus: data ini masih dirujuk oleh pengguna atau aktivitas, termasuk arsip.');
                return;
            }
            document.getElementById('deleteForm').action = btn.dataset.url;
            document.getElementById('deleteName').textContent = btn.dataset.name;
            deleteModal.showModal();
        });
    });

    document.querySelectorAll('[data-close]').forEach(function (el) {
        el.addEventListener('click', function () { el.closest('dialog').close(); });
    });
    document.querySelectorAll('dialog').forEach(function (d) {
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });

    var menuBtn = document.getElementById('menuBtn'), sidebar = document.getElementById('sidebar');
    menuBtn.addEventListener('click', function () { sidebar.classList.toggle('mobile-open'); });

    @if (session('success'))
        toast(@json(session('success')));
    @elseif (session('error'))
        toast(@json(session('error')));
    @elseif ($errors->any())
        toast(@json($errors->first()));
    @endif

    @if ($errors->any() && $reopen === 'add')
        addModal.showModal();
    @elseif ($errors->any() && $reopen === 'edit' && $editingId)
        (function () {
            var src = document.querySelector('.js-edit[data-id="{{ $editingId }}"]');
            if (src) {
                editForm.action = src.dataset.url;
                var locked = src.dataset.locked === '1';
                editName.readOnly = locked;
                editLocked.style.display = locked ? 'block' : 'none';
            }
            editModal.showModal();
        })();
    @endif
})();
</script>
</body>
</html>