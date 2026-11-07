<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Data master SafeTrack - Kelola shift kerja, departemen, kategori temuan, dan area pertanyaan keselamatan.">
    <title>Data Master – SafeTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @include('shared.admin-styles')
</head>
<body>
<div class="app-shell">

    <!-- ══ Sidebar SafeTrack ══ -->
    @include('shared.admin-shell')

        <!-- Canvas Container -->
        <div class="content-container">

            <!-- Title Header Row (Foto 1) -->
            <div class="page-header-row">
                <div class="page-header-text">
                    <div class="page-category-tag">Administrasi</div>
                    <h1 class="page-main-title">Data master</h1>
                    <p class="page-desc-text">Satu referensi data untuk aktivitas keselamatan yang konsisten.</p>
                </div>
                <button type="button" class="btn-add-primary" onclick="openAddModal()" id="btnTambahShift">
                    + Tambah shift
                </button>
            </div>

            <!-- 4 Sub-Tabs Nav Pills (Foto 1) -->
            @include('shared.master-tabs', ['activeMasterTab' => 'shifts'])

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="toast-banner success" role="alert">
                    <div class="toast-banner-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="toast-banner-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="toast-banner error" role="alert">
                    <div class="toast-banner-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="toast-banner-close" onclick="this.parentElement.remove()" aria-label="Tutup notifikasi">&times;</button>
                </div>
            @endif

            <!-- Master Card Table (Foto 1) -->
            <div class="master-card">
                <div class="master-card-header">
                    <div class="card-title-group">
                        <h2>Shift kerja</h2>
                        <p>{{ $shifts->count() }} data tersedia</p>
                    </div>
                </div>

                <table class="master-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Nama</th>
                            <th style="width: 30%;">Jam kerja</th>
                            <th style="width: 20%;">Referensi</th>
                            <th style="width: 15%; text-align: right;"></th>
                        </tr>
                    </thead>
                    <tbody id="shiftTableBody">
                        @forelse ($shifts as $shift)
                            <tr>
                                <td>
                                    <div class="shift-title-block">
                                        <span class="shift-primary-name">{{ $shift->name }}</span>
                                        <span class="shift-code-sub">{{ $shift->code }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="shift-time-text">
                                        {{ substr($shift->starts_at, 0, 5) }} &ndash; {{ substr($shift->ends_at, 0, 5) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($shift->daily_inspections_count > 0)
                                        <span class="ref-status-text active">
                                            Digunakan ({{ $shift->daily_inspections_count }} inspeksi)
                                        </span>
                                    @else
                                        <span class="ref-status-text">Belum digunakan</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button type="button" class="btn-table-action edit" onclick="openEditModal({{ json_encode($shift) }})">Ubah</button>
                                        <button type="button" class="btn-table-action delete" onclick="confirmDeleteShift({{ $shift->id }}, '{{ addslashes($shift->name) }}', {{ $shift->daily_inspections_count }})">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 48px; color: #94a3b8;">
                                    Belum ada data shift kerja. Klik "+ Tambah shift" untuk menambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="card-bottom-note">
                    Data yang sudah dirujuk aktivitas tidak dapat dihapus atau diganti namanya agar riwayat tetap konsisten.
                </div>
            </div>

            <!-- Page System Footer -->
            @include('shared.admin-footer')

        </div>
    </main>

</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Tambah Shift (Matching Foto 2 dengan Time Picker Foto) -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="addShiftModal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="addModalTitle">Tambah shift</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('addShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('shifts.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_name" class="form-group-label">Nama shift</label>
                    <input type="text" id="add_name" name="name" class="form-text-input {{ $errors->has('name') && !old('_edit_id') ? 'has-error' : '' }}" placeholder="Masukkan nama shift..." value="{{ old('name') }}" required autofocus>
                    @if ($errors->has('name') && !old('_edit_id'))
                        <div class="form-error-msg">{{ $errors->first('name') }}</div>
                    @endif
                </div>

                <div class="form-row-time">
                    <!-- Jam Mulai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="add_starts_at" value="{{ old('starts_at', '07:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_start')">
                                <input type="text" id="add_start_display" class="time-val-display" value="{{ old('starts_at', '07:00') }}" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Popover Pemilih Jam Sesuai Foto -->
                            <div class="time-popover-box" id="add_start_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Mulai</span>
                                    <span class="popover-preview-badge" id="add_start_badge">{{ old('starts_at', '07:00') }}</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '07', '00')">07:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '08', '00')">08:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_start', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="add_start_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="add_start_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('starts_at') && !old('_edit_id'))
                            <div class="form-error-msg">{{ $errors->first('starts_at') }}</div>
                        @endif
                    </div>

                    <!-- Jam Selesai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="add_ends_at" value="{{ old('ends_at', '15:00') }}">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_end')">
                                <input type="text" id="add_end_display" class="time-val-display" value="{{ old('ends_at', '15:00') }}" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Popover Pemilih Jam Selesai Sesuai Foto -->
                            <div class="time-popover-box" id="add_end_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Selesai</span>
                                    <span class="popover-preview-badge" id="add_end_badge">{{ old('ends_at', '15:00') }}</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '16', '00')">16:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '17', '00')">17:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('add_end', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="add_end_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="add_end_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                        @if ($errors->has('ends_at') && !old('_edit_id'))
                            <div class="form-error-msg">{{ $errors->first('ends_at') }}</div>
                        @endif
                    </div>
                </div>

                <div class="time-helper-note">
                    Jam selesai yang lebih awal dari jam mulai berarti shift melewati tengah malam.
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('addShiftModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Simpan data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Ubah Shift (Matching Foto 2 dengan Time Picker)        -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="editShiftModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title" id="editModalTitle">Ubah shift</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('editShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="editShiftForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="_edit_id" value="1">
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_name" class="form-group-label">Nama shift</label>
                    <input type="text" id="edit_name" name="name" class="form-text-input" required>
                    <div id="edit_name_warning" style="display:none; font-size:12px; color:#d97706; margin-top:4px;">
                        Nama shift terkunci karena sudah dirujuk inspeksi harian.
                    </div>
                </div>

                <div class="form-row-time">
                    <!-- Jam Mulai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam mulai</label>
                        <input type="hidden" name="starts_at" id="edit_starts_at" value="07:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_start')">
                                <input type="text" id="edit_start_display" class="time-val-display" value="07:00" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="time-popover-box" id="edit_start_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Mulai</span>
                                    <span class="popover-preview-badge" id="edit_start_badge">07:00</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '07', '00')">07:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '08', '00')">08:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_start', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="edit_start_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="edit_start_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-group-label">Jam selesai</label>
                        <input type="hidden" name="ends_at" id="edit_ends_at" value="15:00">
                        <div class="time-picker-wrapper">
                            <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_end')">
                                <input type="text" id="edit_end_display" class="time-val-display" value="15:00" readonly>
                                <div class="time-clock-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="time-popover-box" id="edit_end_popover">
                                <div class="popover-header">
                                    <span>Pilih Jam Selesai</span>
                                    <span class="popover-preview-badge" id="edit_end_badge">15:00</span>
                                </div>
                                <div class="preset-row">
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '15', '00')">15:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '16', '00')">16:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '17', '00')">17:00</span>
                                    <span class="preset-chip" onclick="applyPresetTime('edit_end', '23', '00')">23:00</span>
                                </div>
                                <div class="time-columns-container">
                                    <div class="time-column">
                                        <div class="time-column-label">JAM</div>
                                        <div class="time-column-scroll" id="edit_end_h_list"></div>
                                    </div>
                                    <div class="time-col-sep">:</div>
                                    <div class="time-column">
                                        <div class="time-column-label">MENIT</div>
                                        <div class="time-column-scroll" id="edit_end_m_list"></div>
                                    </div>
                                </div>
                                <div class="popover-footer-actions">
                                    <button type="button" class="btn-popover-done" onclick="closeAllTimePickers()">Selesai</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="time-helper-note">
                    Jam selesai yang lebih awal dari jam mulai berarti shift melewati tengah malam.
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('editShiftModal')">Batal</button>
                    <button type="submit" class="btn-modal-save">Simpan data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- MODAL: Hapus Shift Konfirmasi                                -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="deleteShiftModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
    <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
            <h2 class="modal-title" id="deleteModalTitle">Hapus shift kerja</h2>
            <button type="button" class="modal-close-circle" onclick="closeModal('deleteShiftModal')" aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="deleteShiftForm">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <div id="deleteWarningMessage" style="font-size:13.5px; color:#475569; line-height:1.5;">
                    Apakah Anda yakin ingin menghapus shift kerja ini? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer-actions" style="margin-top:20px;">
                    <button type="button" class="btn-modal-cancel" onclick="closeModal('deleteShiftModal')" id="btnCancelDelete">Batal</button>
                    <button type="submit" class="btn-modal-save" id="btnConfirmDelete" style="background-color:#b91c1c;">Hapus shift</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('open');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('open');
    }
    function openAddModal() {
        openModal('addShiftModal');
        setTimeout(function() {
            var input = document.getElementById('add_name');
            if (input) input.focus();
        }, 100);
    }

    function openEditModal(shift) {
        var form = document.getElementById('editShiftForm');
        form.action = '/shifts/' + shift.id;
        document.getElementById('edit_name').value = shift.name;

        var startVal = (shift.starts_at || '07:00').substring(0, 5);
        var endVal = (shift.ends_at || '15:00').substring(0, 5);

        document.getElementById('edit_starts_at').value = startVal;
        document.getElementById('edit_start_display').value = startVal;
        document.getElementById('edit_start_badge').textContent = startVal;

        document.getElementById('edit_ends_at').value = endVal;
        document.getElementById('edit_end_display').value = endVal;
        document.getElementById('edit_end_badge').textContent = endVal;

        applyPresetTime('edit_start', startVal.substring(0, 2), startVal.substring(3, 5));
        applyPresetTime('edit_end', endVal.substring(0, 2), endVal.substring(3, 5));

        // Lock name if referenced
        var warning = document.getElementById('edit_name_warning');
        var nameInput = document.getElementById('edit_name');
        if (shift.daily_inspections_count > 0) {
            warning.style.display = 'block';
            nameInput.readOnly = true;
            nameInput.style.backgroundColor = '#f8fafc';
        } else {
            warning.style.display = 'none';
            nameInput.readOnly = false;
            nameInput.style.backgroundColor = '#ffffff';
        }

        openModal('editShiftModal');
    }

    function confirmDeleteShift(id, name, inspectionsCount) {
        var form = document.getElementById('deleteShiftForm');
        var msg = document.getElementById('deleteWarningMessage');
        var confirmBtn = document.getElementById('btnConfirmDelete');
        var cancelBtn = document.getElementById('btnCancelDelete');

        if (inspectionsCount > 0) {
            form.action = '#';
            form.onsubmit = function(e) { e.preventDefault(); closeModal('deleteShiftModal'); };
            msg.innerHTML = '<span style="color:#ef4444; font-weight:700;">Tidak dapat dihapus!</span><br>Shift <strong>' + name + '</strong> sudah dirujuk dalam ' + inspectionsCount + ' inspeksi harian. Sesuai regulasi data keselamatan, data yang telah dirujuk tidak dapat dihapus.';
            confirmBtn.style.display = 'none';
            cancelBtn.textContent = 'Mengerti';
        } else {
            form.action = '/shifts/' + id;
            form.onsubmit = null;
            msg.innerHTML = 'Apakah Anda yakin ingin menghapus shift <strong>' + name + '</strong>?<br>Tindakan ini tidak dapat dibatalkan.';
            confirmBtn.style.display = 'inline-flex';
            cancelBtn.textContent = 'Batal';
        }

        openModal('deleteShiftModal');
    }

    /* Close on backdrop or escape */
    document.querySelectorAll('.modal-backdrop').forEach(function(m) {
        m.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.open').forEach(function(m) { closeModal(m.id); });
            closeAllTimePickers();
        }
    });

    /* ── Custom Time Picker Core Logic ── */
    var timePickerPickers = ['add_start', 'add_end', 'edit_start', 'edit_end'];
    var minuteSteps = ['00', '15', '30', '45'];

    function initTimePickers() {
        timePickerPickers.forEach(function(pfx) {
            var hScroll = document.getElementById(pfx + '_h_list');
            var mScroll = document.getElementById(pfx + '_m_list');
            if (!hScroll || !mScroll) return;

            hScroll.innerHTML = '';
            mScroll.innerHTML = '';

            for (var h = 0; h < 24; h++) {
                var hh = String(h).padStart(2, '0');
                var hItem = document.createElement('div');
                hItem.className = 'time-scroll-item';
                hItem.dataset.hour = hh;
                hItem.textContent = hh;
                hItem.onclick = (function(prefix, val) {
                    return function(ev) {
                        ev.stopPropagation();
                        selectHour(prefix, val);
                    };
                })(pfx, hh);
                hScroll.appendChild(hItem);
            }

            minuteSteps.forEach(function(mm) {
                var mItem = document.createElement('div');
                mItem.className = 'time-scroll-item';
                mItem.dataset.minute = mm;
                mItem.textContent = mm;
                mItem.onclick = (function(prefix, val) {
                    return function(ev) {
                        ev.stopPropagation();
                        selectMinute(prefix, val);
                    };
                })(pfx, mm);
                mScroll.appendChild(mItem);
            });

            var curVal = document.getElementById(pfx + '_display').value || '07:00';
            applyPresetTime(pfx, curVal.substring(0, 2), curVal.substring(3, 5));
        });
    }

    function openTimePickerPopover(e, pfx) {
        e.stopPropagation();
        var popover = document.getElementById(pfx + '_popover');
        var wasOpen = popover.classList.contains('open');
        closeAllTimePickers();
        if (!wasOpen) {
            popover.classList.add('open');
            var activeH = popover.querySelector('.time-column-scroll .time-scroll-item.selected');
            if (activeH) activeH.scrollIntoView({ block: 'nearest' });
        }
    }

    function closeAllTimePickers() {
        document.querySelectorAll('.time-popover-box.open').forEach(function(pop) {
            pop.classList.remove('open');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.time-picker-wrapper')) {
            closeAllTimePickers();
        }
    });

    function selectHour(pfx, h) {
        var hScroll = document.getElementById(pfx + '_h_list');
        hScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.hour === h);
        });
        updateTimeCombinedValue(pfx);
    }

    function selectMinute(pfx, m) {
        var mScroll = document.getElementById(pfx + '_m_list');
        mScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.minute === m);
        });
        updateTimeCombinedValue(pfx);
    }

    function updateTimeCombinedValue(pfx) {
        var hSelected = document.querySelector('#' + pfx + '_h_list .time-scroll-item.selected');
        var mSelected = document.querySelector('#' + pfx + '_m_list .time-scroll-item.selected');
        var h = hSelected ? hSelected.dataset.hour : '07';
        var m = mSelected ? mSelected.dataset.minute : '00';
        var combined = h + ':' + m;

        document.getElementById(pfx + '_display').value = combined;
        document.getElementById(pfx + '_badge').textContent = combined;

        var formField = (pfx.indexOf('start') !== -1) ? 'starts_at' : 'ends_at';
        var scope = (pfx.indexOf('add') !== -1) ? 'add' : 'edit';
        document.getElementById(scope + '_' + formField).value = combined;
    }

    function applyPresetTime(pfx, h, m) {
        var hScroll = document.getElementById(pfx + '_h_list');
        var mScroll = document.getElementById(pfx + '_m_list');
        if (!hScroll || !mScroll) return;

        hScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.hour === h);
        });
        mScroll.querySelectorAll('.time-scroll-item').forEach(function(item) {
            item.classList.toggle('selected', item.dataset.minute === m);
        });
        updateTimeCombinedValue(pfx);
    }

    document.addEventListener('DOMContentLoaded', function() {
        initTimePickers();
        @if ($errors->any() && !old('_edit_id'))
            openAddModal();
        @endif
    });
</script>
</body>
</html>
