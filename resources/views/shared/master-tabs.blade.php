<div class="master-tabs-bar">
                <button type="button" class="tab-pill" onclick="alert('Modul Departemen dalam integrasi Data Master.')">
                    <span>Departemen</span>
                    <span class="tab-pill-badge">{{ $tabCounts['departments'] ?? 2 }}</span>
                </button>
                <a href="{{ route('shifts.index') }}" class="tab-pill {{ $activeMasterTab === 'shifts' ? 'active' : '' }}" style="text-decoration:none;">
                    <span>Shift kerja</span>
                    <span class="tab-pill-badge">{{ $tabCounts['shifts'] ?? 0 }}</span>
                </a>
                <button type="button" class="tab-pill" onclick="alert('Modul Kategori Temuan dalam integrasi Data Master.')">
                    <span>Kategori temuan</span>
                    <span class="tab-pill-badge">{{ $tabCounts['hazard_categories'] ?? 2 }}</span>
                </button>
                <a href="{{ route('admin.work-areas.index') }}" class="tab-pill {{ $activeMasterTab === 'areas' ? 'active' : '' }}" style="text-decoration:none;">
                    <span>Area &amp; pertanyaan</span>
                    <span class="tab-pill-badge">{{ $tabCounts['work_areas'] ?? 16 }}</span>
                </a>
            </div>