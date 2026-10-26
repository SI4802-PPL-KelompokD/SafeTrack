<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inspeksi Harian – SafeTrack</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f7f9fc;color:#334155;font:14px system-ui,-apple-system,sans-serif}.app-shell{display:flex;min-height:100vh}.sidebar{position:fixed;inset:0 auto 0 0;width:260px;background:#fff;border-right:1px solid #e2e8f0;display:flex;flex-direction:column;z-index:40}.brand{display:flex;align-items:center;gap:12px;padding:20px}.brand-mark{width:36px;height:36px;border-radius:9px;background:#0f2a4a;color:#f59e0b;display:grid;place-items:center;font-size:20px}.brand-name{color:#0f2a4a;font-size:18px;font-weight:800}.brand-name span{color:#d97706}.brand-sub{font-size:9px;font-weight:700;color:#64748b}.workspace{margin:0 16px 16px;padding:11px 12px;border:1px solid #e2e8f0;border-radius:10px}.workspace strong{display:block;color:#0f172a;font-size:13px}.workspace small{color:#94a3b8}.nav-title{padding:12px 20px 6px;color:#94a3b8;font-size:11px;font-weight:700;text-transform:uppercase}.nav{list-style:none;margin:0;padding:0 12px}.nav a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:7px;color:#475569;text-decoration:none}.nav a:hover{background:#f1f5f9}.nav a.active{background:#0f2a4a;color:#fff;font-weight:700}.sidebar-user{margin-top:auto;padding:14px 16px;border-top:1px solid #e2e8f0;display:flex;align-items:center;gap:10px}.avatar{width:34px;height:34px;border-radius:50%;background:#fef3c7;color:#92400e;display:grid;place-items:center;font-size:11px;font-weight:700}.logout{margin-left:auto;border:0;background:none;color:#94a3b8;font-size:18px;cursor:pointer}.main-shell{margin-left:260px;min-width:0;flex:1}.topbar{height:65px;background:#fff;border-bottom:1px solid #e2e8f0;padding:0 32px;display:flex;align-items:center;justify-content:space-between}.page{max-width:1200px;padding:34px 36px}.head,.actions,.row{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}h1{margin:4px 0 0;color:#0f172a;font-size:28px}.muted{color:#64748b}.btn{border:0;border-radius:7px;background:#132f52;color:#fff;padding:11px 17px;font-weight:700;cursor:pointer;text-decoration:none}.btn.secondary{background:#fff;color:#334155;border:1px solid #cbd5e1}.card{margin-top:28px;background:#fff;border:1px solid #dbe3ed;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px #0f172a0d}.card-title{padding:18px;border-bottom:1px solid #e2e8f0}.card-title h2{margin:0;color:#0f172a;font-size:17px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse}th,td{padding:14px 18px;border-bottom:1px solid #e2e8f0;text-align:left}th{background:#f8fafc;color:#64748b;font-size:12px;text-transform:uppercase}.badge{display:inline-block;border-radius:999px;background:#fef3c7;color:#92400e;padding:4px 9px;font-size:12px}.empty{padding:50px;text-align:center;color:#64748b}.pagination{padding:16px}.alert{padding:13px 16px;border-radius:7px;margin-top:16px;background:#ecfdf5;color:#065f46}.alert.error{background:#fef2f2;color:#991b1b}.modal-layer{display:none;position:fixed;inset:0;background:#0f172a99;z-index:100;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}.modal-layer.open{display:flex}.modal{background:#fff;width:min(760px,96vw);max-height:92vh;border-radius:15px;box-shadow:0 24px 70px #0f172a55;overflow:hidden}.modal-head{padding:18px 26px;border-bottom:1px solid #e2e8f0}.modal-head h2{margin:0;color:#0f172a;font-size:19px}.close{border:2px solid #86a3c7;border-radius:12px;background:#fff;color:#64748b;width:42px;height:42px;font-size:22px;cursor:pointer}.modal-body{padding:25px 28px;max-height:calc(92vh - 78px);overflow:auto}.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:28px}.step{padding:9px;border-bottom:3px solid #e2e8f0;color:#94a3b8;font-size:12px}.step.active{border-color:#315d96;color:#17365d}.step span{display:inline-grid;place-items:center;width:21px;height:21px;border-radius:50%;background:#eef2f7;margin-right:6px}.step.active span{background:#17365d;color:#fff}.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.field{display:grid;gap:7px;color:#475569;font-size:13px}.full{grid-column:1/-1}.input,.select,.textarea{width:100%;padding:11px 12px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#334155;font:inherit}.textarea{min-height:76px;resize:vertical}.question{padding:16px 0;border-top:1px solid #e2e8f0}.question:first-child{border-top:0}.answer-options{display:flex;gap:20px;margin:12px 0}.condition{display:none;grid-template-columns:1fr 1fr;gap:12px}.condition.visible{display:grid}.info{margin-top:16px;padding:14px;background:#eff6ff;border:1px solid #cfe0f5;border-radius:7px;color:#54708f}.modal-actions{display:flex;justify-content:space-between;align-items:center;border-top:1px solid #e2e8f0;margin-top:24px;padding-top:18px}.hidden{display:none!important}@media(max-width:760px){.sidebar{display:none}.main-shell{margin-left:0}.topbar{padding:0 18px}.page{padding:24px 16px}.grid,.condition{grid-template-columns:1fr}.full{grid-column:auto}.modal-body{padding:20px}.steps{font-size:11px}}
    </style>
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
    <div class="brand"><div class="brand-mark">&#x1F6E1;</div><div><div class="brand-name">safe<span>Track</span></div><div class="brand-sub">SAFETY MANAGEMENT</div></div></div>
    <div class="workspace"><strong>PT Sentra Industri</strong><small>Workspace demonstrasi</small></div>
    <div class="nav-title">Utama</div>
    <ul class="nav">
        <li><a href="{{ route('dashboard') }}">&#9638; <span>Ringkasan</span></a></li>
        <li><a href="#">&#9888; <span>Laporan Bahaya</span></a></li>
        <li><a class="active" href="{{ route('daily-inspections.index') }}">&#9745; <span>Inspeksi Harian</span></a></li>
        <li><a href="#">&#9781; <span>Safety Talk</span></a></li>
        <li><a href="#">&#10022; <span>Pencapaian SAP</span></a></li>
    </ul>
    @if($isAdmin)
        <div class="nav-title">Administrasi</div>
        <ul class="nav">
            <li><a href="{{ route('shifts.index') }}">&#9716; <span>Kelola Shift Kerja</span></a></li>
            <li><a href="{{ route('admin.work-areas.index') }}">&#9638; <span>Data Master</span></a></li>
        </ul>
    @endif
    <div class="sidebar-user"><div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div><div><strong>{{ auth()->user()->name }}</strong><br><small>{{ $isAdmin ? 'Admin' : 'Karyawan' }}</small></div><form method="POST" action="{{ route('logout') }}" style="margin-left:auto">@csrf<button class="logout" title="Keluar">&#10162;</button></form></div>
</aside>
<div class="main-shell">
<header class="topbar"><div class="muted">Workspace &gt; <strong style="color:#0f172a">Inspeksi Harian</strong></div><div class="row"><span class="muted">Halo, <strong>{{ auth()->user()->name }}</strong></span><div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div></div></header>
<main class="page">
    <div class="head">
        <div><div class="muted">Workspace › Inspeksi Harian</div><h1>Inspeksi Harian</h1><p class="muted">{{ $isAdmin ? 'Verifikasi hasil inspeksi yang dikirim akun pribadi pengguna.' : 'Isi checklist keselamatan berdasarkan area kerja.' }}</p></div>
        @unless($isAdmin)<button id="openInspection" class="btn" type="button">+ Buat Inspeksi</button>@endunless
    </div>
    @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
    <section class="card">
        <div class="card-title"><h2>Daftar inspeksi</h2><div class="muted">{{ $isAdmin ? 'Seluruh inspeksi di lingkungan kerja.' : 'Riwayat inspeksi yang Anda kirim.' }}</div></div>
        @if($inspections->isEmpty())
            <div class="empty">Belum ada inspeksi yang dikirim.</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Inspektor</th><th>Area</th><th>Shift</th><th>Status</th></tr></thead><tbody>
            @foreach($inspections as $inspection)<tr><td>{{ $inspection->inspection_date }}</td><td>{{ $inspection->inspector->name }}</td><td>{{ $inspection->workArea->name }}</td><td>{{ $inspection->shift->name }}</td><td><span class="badge">{{ ucfirst($inspection->status) }}</span></td></tr>@endforeach
            </tbody></table></div><div class="pagination">{{ $inspections->links() }}</div>
        @endif
    </section>
</main>
</div>
</div>

@unless($isAdmin)
<div id="inspectionModal" class="modal-layer {{ $openCreateModal || $errors->any() ? 'open' : '' }}" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <section class="modal">
        <header class="modal-head row"><h2 id="modalTitle">Buat inspeksi harian</h2><button id="closeInspection" class="close" type="button" aria-label="Tutup">×</button></header>
        <form method="POST" action="{{ route('daily-inspections.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                @if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif
                <div class="steps"><div id="stepLabel1" class="step active"><span>1</span>Pilih area</div><div id="stepLabel2" class="step"><span>2</span>Isi checklist</div><div class="step"><span>3</span>Konfirmasi</div></div>
                <div id="selectionStep">
                    <div class="grid">
                        <label class="field">Area kerja *<select id="workArea" class="select" name="work_area_id" required><option value="">Pilih area yang diperiksa</option>@foreach($workAreas as $area)<option value="{{ $area->id }}" data-url="{{ route('work-areas.active-questions',$area) }}" @selected(old('work_area_id')==$area->id)>{{ $area->name }}</option>@endforeach</select></label>
                        <label class="field">Shift kerja *<select class="select" name="shift_id" required><option value="">Pilih shift</option>@foreach($shifts as $shift)<option value="{{ $shift->id }}" @selected(old('shift_id')==$shift->id)>{{ $shift->name }}</option>@endforeach</select></label>
                        <label class="field">Tanggal inspeksi *<input class="input" type="date" name="inspection_date" max="{{ now()->toDateString() }}" value="{{ old('inspection_date',now()->toDateString()) }}" required></label>
                        <label class="field full">Keterangan area kerja<textarea class="textarea" name="notes" placeholder="Opsional: lokasi spesifik atau kondisi umum area">{{ old('notes') }}</textarea></label>
                    </div>
                    <div class="info"><strong>Checklist mengikuti area Anda</strong><br>Pertanyaan dimuat dari data master aktif dan diurutkan otomatis.</div>
                    <div class="modal-actions"><button class="btn secondary cancel" type="button">Batal</button><button id="nextStep" class="btn" type="button">Lanjut ke checklist →</button></div>
                </div>
                <div id="checklistStep" class="hidden"><div id="questions"><p class="muted">Pilih area kerja terlebih dahulu.</p></div><div class="modal-actions"><button id="backStep" class="btn secondary" type="button">← Kembali</button><button id="submitInspection" class="btn" disabled>Kirim inspeksi</button></div></div>
            </div>
        </form>
    </section>
</div>
<script>
const modal=document.getElementById('inspectionModal'),area=document.getElementById('workArea'),questions=document.getElementById('questions'),submit=document.getElementById('submitInspection'),selection=document.getElementById('selectionStep'),checklist=document.getElementById('checklistStep');
const closeModal=()=>modal.classList.remove('open');document.getElementById('openInspection').addEventListener('click',()=>modal.classList.add('open'));document.getElementById('closeInspection').addEventListener('click',closeModal);document.querySelector('.cancel').addEventListener('click',closeModal);
document.getElementById('nextStep').addEventListener('click',async()=>{if(!area.value){area.reportValidity();return}const option=area.selectedOptions[0];questions.innerHTML='<p class="muted">Memuat pertanyaan...</p>';try{const response=await fetch(option.dataset.url,{headers:{Accept:'application/json'}});if(!response.ok)throw new Error();const payload=await response.json();if(!payload.data.length){questions.innerHTML='<p class="alert error">Belum ada pertanyaan aktif untuk area ini.</p>';return}questions.innerHTML=payload.data.map((q,i)=>`<div class="question"><strong>${i+1}. ${escapeHtml(q.question)}</strong><input type="hidden" name="answers[${i}][question_id]" value="${q.id}"><div class="answer-options"><label><input type="radio" name="answers[${i}][answer]" value="yes" required> Ya</label><label><input type="radio" name="answers[${i}][answer]" value="no" required> Tidak</label></div><div id="condition-${i}" class="condition"><label class="field">Deskripsi kondisi *<textarea class="textarea" name="answers[${i}][description]"></textarea></label><label class="field">Foto kondisi *<input class="input" type="file" accept="image/*" name="answers[${i}][photo]"></label></div></div>`).join('');questions.querySelectorAll('input[type=radio]').forEach(radio=>radio.addEventListener('change',()=>{const i=radio.name.match(/\d+/)[0],detail=document.getElementById(`condition-${i}`),required=radio.checked&&radio.value==='no';detail.classList.toggle('visible',required);detail.querySelectorAll('textarea,input[type=file]').forEach(input=>input.required=required)}));selection.classList.add('hidden');checklist.classList.remove('hidden');stepLabel1.classList.remove('active');stepLabel2.classList.add('active');submit.disabled=false}catch{questions.innerHTML='<p class="alert error">Pertanyaan gagal dimuat. Silakan coba kembali.</p>'}});
document.getElementById('backStep').addEventListener('click',()=>{checklist.classList.add('hidden');selection.classList.remove('hidden');stepLabel2.classList.remove('active');stepLabel1.classList.add('active')});function escapeHtml(value){const div=document.createElement('div');div.textContent=value;return div.innerHTML}
</script>
@endunless
</body></html>
