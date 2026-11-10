@extends('layouts.safetrack', ['title' => 'Shift Kerja', 'pageTitle' => 'Data Master'])
@section('content')
@vite(['resources/css/shifts.css', 'resources/js/shifts.js'])
<div class="shift-content" aria-label="Kelola shift kerja">
    <div class="page-head">
        <div class="page-header-text">
            <div class="eyebrow">Administrasi</div>
            <h1 class="page-main-title">Data master</h1>
            <p class="page-desc-text">Kelola referensi data keselamatan kerja.</p>
        </div>
        <button type="button" class="btn primary" data-open-dialog="create-shift">+ Tambah shift</button>
    </div>

    <div class="master-tabs-bar">
        <a class="master-tab-item" href="{{ route('admin.departments.index') }}">Departemen</a>
        <span class="master-tab-item active">Shift kerja</span>
        <a class="master-tab-item" href="{{ route('admin.work-areas.index') }}">Area &amp; pertanyaan</a>
    </div>

    <section class="panel">
        <form class="shift-search" method="GET" action="{{ route('shifts.index') }}">
            <label>
                <span aria-hidden="true">⌕</span>
                <input name="search" value="{{ request('search') }}" placeholder="Cari nama shift..." aria-label="Cari nama shift">
            </label>
            <button class="btn">Cari</button>
            <a class="btn ghost" href="{{ route('shifts.index') }}">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 32%;">Nama</th>
                        <th style="width: 26%;">Jam kerja</th>
                        <th style="width: 22%;">Diperbarui</th>
                        <th style="width: 20%;" class="shift-action-heading">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr data-shift-id="{{ $shift->id }}" data-active="{{ (int) $shift->is_active }}">
                            <td><strong>{{ $shift->name }}</strong></td>
                            <td>{{ substr($shift->starts_at, 0, 5) }} &ndash; {{ substr($shift->ends_at, 0, 5) }}</td>
                            <td>{{ $shift->updated_at?->translatedFormat('d M Y') }}</td>
                            <td>
                                <div class="shift-actions">
                                    <button type="button" class="shift-edit" data-edit-shift data-id="{{ $shift->id }}" data-name="{{ $shift->name }}" data-starts="{{ substr($shift->starts_at, 0, 5) }}" data-ends="{{ substr($shift->ends_at, 0, 5) }}" data-url="{{ route('shifts.update', $shift) }}">Ubah</button>
                                    <form method="POST" action="{{ route('shifts.toggle-status', $shift) }}" data-toggle-form data-confirm-title="{{ $shift->is_active ? 'Nonaktifkan' : 'Aktifkan' }} shift kerja?" data-confirm-entity="{{ $shift->name }}" data-confirm-label="{{ $shift->is_active ? 'Nonaktifkan shift kerja' : 'Aktifkan shift kerja' }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $shift->is_active ? 0 : 1 }}">
                                        <button class="shift-switch {{ $shift->is_active ? 'is-on' : '' }}" type="submit" role="switch" aria-checked="{{ $shift->is_active ? 'true' : 'false' }}" aria-label="{{ $shift->is_active ? 'Nonaktifkan' : 'Aktifkan' }} shift {{ $shift->name }}">
                                            <span aria-hidden="true">{{ $shift->is_active ? 'ON' : 'OFF' }}</span>
                                            <i aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty">Tidak ada shift yang sesuai. Gunakan pencarian lain atau tambahkan shift baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="shift-pagination">{{ $shifts->links('shifts.pagination') }}</div>
    </section>

    <footer class="shift-footer">
        <span>&copy; {{ date('Y') }} SafeTrack. Setiap kepedulian berarti.</span>
        <span class="system-status"><span class="status-dot"></span> Sistem aktif &middot; Data tersimpan otomatis</span>
    </footer>
</div>

{{-- Modal Tambah --}}
<dialog id="create-shift" class="wa-dialog" aria-labelledby="create-shift-title" @if($errors->any() && old('_form') === 'create') data-reopen @endif>
    <header>
        <h2 id="create-shift-title">Tambah shift kerja</h2>
        <button type="button" data-close-dialog aria-label="Tutup">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 1L13 13M1 13L13 1"/>
            </svg>
        </button>
    </header>
    <form method="POST" action="{{ route('shifts.store') }}" id="create-shift-form">
        @csrf
        <input type="hidden" name="_form" value="create">
        <div class="wa-dialog-body">
            <label>
                <span class="label-text">Nama shift <span class="req">*</span></span>
                <input name="name" id="add_name" required maxlength="50" value="{{ old('_form') === 'create' ? old('name') : '' }}" placeholder="Contoh: Shift Pagi">
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </label>

            <div class="form-row-time">
                {{-- Jam Mulai Custom Picker --}}
                <label>
                    <span class="label-text">Jam mulai <span class="req">*</span></span>
                    <input type="hidden" name="starts_at" id="add_starts_at" value="{{ old('_form') === 'create' ? old('starts_at', '07:00') : '07:00' }}">
                    <div class="time-picker-wrapper">
                        <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_start')">
                            <input type="text" id="add_start_display" class="time-val-display" value="{{ old('_form') === 'create' ? old('starts_at', '07:00') : '07:00' }}" readonly>
                            <div class="time-clock-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="time-popover-box" id="add_start_popover">
                            <div class="popover-header">
                                <span>PILIH JAM MULAI</span>
                                <span class="popover-preview-badge" id="add_start_badge">{{ old('_form') === 'create' ? old('starts_at', '07:00') : '07:00' }}</span>
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
                    @error('starts_at')<small class="field-error">{{ $message }}</small>@enderror
                </label>

                {{-- Jam Selesai Custom Picker --}}
                <label>
                    <span class="label-text">Jam selesai <span class="req">*</span></span>
                    <input type="hidden" name="ends_at" id="add_ends_at" value="{{ old('_form') === 'create' ? old('ends_at', '15:00') : '15:00' }}">
                    <div class="time-picker-wrapper">
                        <div class="time-input-box" onclick="openTimePickerPopover(event, 'add_end')">
                            <input type="text" id="add_end_display" class="time-val-display" value="{{ old('_form') === 'create' ? old('ends_at', '15:00') : '15:00' }}" readonly>
                            <div class="time-clock-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="time-popover-box" id="add_end_popover">
                            <div class="popover-header">
                                <span>PILIH JAM SELESAI</span>
                                <span class="popover-preview-badge" id="add_end_badge">{{ old('_form') === 'create' ? old('ends_at', '15:00') : '15:00' }}</span>
                            </div>
                            <div class="preset-row">
                                <span class="preset-chip" onclick="applyPresetTime('add_end', '07', '00')">07:00</span>
                                <span class="preset-chip" onclick="applyPresetTime('add_end', '08', '00')">08:00</span>
                                <span class="preset-chip" onclick="applyPresetTime('add_end', '15', '00')">15:00</span>
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
                    @error('ends_at')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>

            <label>
                <span class="label-text">Nama petugas Admin <span class="req">*</span></span>
                <input name="admin_name" required minlength="3" maxlength="100" autocomplete="name" placeholder="Ketik nama petugas Admin" value="{{ old('_form') === 'create' ? old('admin_name') : '' }}">
                @error('admin_name')<small class="field-error">{{ $message }}</small>@enderror
            </label>

            <label>
                <span class="label-text">Alasan penambahan <span class="req">*</span></span>
                <textarea name="action_reason" required minlength="10" maxlength="1000" rows="3" placeholder="Jelaskan alasan penambahan, minimal 10 karakter">{{ old('_form') === 'create' ? old('action_reason') : '' }}</textarea>
                @error('action_reason')<small class="field-error">{{ $message }}</small>@enderror
            </label>
        </div>
        <footer>
            <button type="button" class="btn ghost" data-close-dialog>Batal</button>
            <button type="submit" class="btn primary">Simpan</button>
        </footer>
    </form>
</dialog>

{{-- Modal Ubah (Media Screenshot 2) --}}
<dialog id="edit-shift" class="wa-dialog" aria-labelledby="edit-shift-title" @if($errors->any() && old('_form') === 'edit') data-reopen @endif>
    <header>
        <h2 id="edit-shift-title">Ubah shift kerja</h2>
        <button type="button" data-close-dialog aria-label="Tutup">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 1L13 13M1 13L13 1"/>
            </svg>
        </button>
    </header>
    <form method="POST" id="edit-shift-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="_form" value="edit">
        <div class="wa-dialog-body">
            <label>
                <span class="label-text">Nama <span class="req">*</span></span>
                <input name="name" id="edit-name" required maxlength="50" value="{{ old('_form') === 'edit' ? old('name') : '' }}" placeholder="Nama shift">
                @error('name')<small class="field-error">{{ $message }}</small>@enderror
            </label>

            <div class="form-row-time">
                {{-- Jam Mulai Custom Picker (Edit) --}}
                <label>
                    <span class="label-text">Jam mulai <span class="req">*</span></span>
                    <input type="hidden" name="starts_at" id="edit_starts_at" value="{{ old('_form') === 'edit' ? old('starts_at', '07:00') : '07:00' }}">
                    <div class="time-picker-wrapper">
                        <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_start')">
                            <input type="text" id="edit_start_display" class="time-val-display" value="{{ old('_form') === 'edit' ? old('starts_at', '07:00') : '07:00' }}" readonly>
                            <div class="time-clock-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="time-popover-box" id="edit_start_popover">
                            <div class="popover-header">
                                <span>PILIH JAM MULAI</span>
                                <span class="popover-preview-badge" id="edit_start_badge">{{ old('_form') === 'edit' ? old('starts_at', '07:00') : '07:00' }}</span>
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
                    @error('starts_at')<small class="field-error">{{ $message }}</small>@enderror
                </label>

                {{-- Jam Selesai Custom Picker (Edit) --}}
                <label>
                    <span class="label-text">Jam selesai <span class="req">*</span></span>
                    <input type="hidden" name="ends_at" id="edit_ends_at" value="{{ old('_form') === 'edit' ? old('ends_at', '15:00') : '15:00' }}">
                    <div class="time-picker-wrapper">
                        <div class="time-input-box" onclick="openTimePickerPopover(event, 'edit_end')">
                            <input type="text" id="edit_end_display" class="time-val-display" value="{{ old('_form') === 'edit' ? old('ends_at', '15:00') : '15:00' }}" readonly>
                            <div class="time-clock-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="time-popover-box" id="edit_end_popover">
                            <div class="popover-header">
                                <span>PILIH JAM SELESAI</span>
                                <span class="popover-preview-badge" id="edit_end_badge">{{ old('_form') === 'edit' ? old('ends_at', '15:00') : '15:00' }}</span>
                            </div>
                            <div class="preset-row">
                                <span class="preset-chip" onclick="applyPresetTime('edit_end', '07', '00')">07:00</span>
                                <span class="preset-chip" onclick="applyPresetTime('edit_end', '08', '00')">08:00</span>
                                <span class="preset-chip" onclick="applyPresetTime('edit_end', '15', '00')">15:00</span>
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
                    @error('ends_at')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>

            <label>
                <span class="label-text">Nama petugas Admin <span class="req">*</span></span>
                <input name="admin_name" id="edit-admin-name" required minlength="3" maxlength="100" autocomplete="name" placeholder="Ketik nama petugas Admin" value="{{ old('_form') === 'edit' ? old('admin_name') : '' }}">
                @error('admin_name')<small class="field-error">{{ $message }}</small>@enderror
            </label>

            <label>
                <span class="label-text">Alasan perubahan <span class="req">*</span></span>
                <textarea name="action_reason" id="edit-action-reason" required minlength="10" maxlength="1000" rows="3" placeholder="Jelaskan alasan perubahan, minimal 10 karakter">{{ old('_form') === 'edit' ? old('action_reason') : '' }}</textarea>
                @error('action_reason')<small class="field-error">{{ $message }}</small>@enderror
            </label>
        </div>
        <footer>
            <button type="button" class="btn ghost" data-close-dialog>Batal</button>
            <button type="submit" class="btn primary">Simpan</button>
        </footer>
    </form>
</dialog>
@endsection

@push('modals')
{{-- Modal Konfirmasi Toggle Status (Media Screenshot 1) --}}
<dialog id="admin-confirm" class="wa-dialog wa-confirm" aria-labelledby="confirm-title">
    <header>
        <h2 id="confirm-title">Aktifkan shift kerja?</h2>
        <button type="button" data-close-dialog aria-label="Tutup">
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M1 1L13 13M1 13L13 1"/>
            </svg>
        </button>
    </header>
    <form id="admin-confirm-form">
        <div class="wa-dialog-body">
            <h3 id="confirm-entity" style="font-weight:700;font-size:16px;margin:0 0 16px;color:#0f172a;">Shift Siang</h3>
            <label>
                <span class="label-text">Nama petugas Admin <span class="req">*</span></span>
                <input name="admin_name" required minlength="3" maxlength="100" autocomplete="name" placeholder="Ketik nama petugas Admin">
            </label>
            <label>
                <span class="label-text">Alasan perubahan <span class="req">*</span></span>
                <textarea name="action_reason" required minlength="10" maxlength="1000" rows="3" placeholder="Jelaskan alasan perubahan, minimal 10 karakter"></textarea>
            </label>
            <p id="identity-error" class="field-error" role="alert"></p>
        </div>
        <footer>
            <button type="button" class="btn ghost" data-close-dialog>Batal</button>
            <button class="btn primary" id="confirm-submit">Aktifkan shift kerja</button>
        </footer>
    </form>
</dialog>
@endpush
