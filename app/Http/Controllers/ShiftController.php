<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\Shift;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Tampilkan daftar shift kerja dalam modul Data Master.
     */
    public function index(): View
    {
        $shifts = Shift::withCount('dailyInspections')
            ->orderBy('id')
            ->get();

        $tabCounts = [
            'departments' => Department::count(),
            'shifts' => Shift::count(),
            'hazard_categories' => HazardCategory::count(),
            'work_areas' => WorkArea::count(),
        ];

        return view('shifts.index', compact('shifts', 'tabCounts'));
    }

    /**
     * Simpan shift kerja baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Auto-generate code if omitted
        $name = trim((string) $request->input('name', ''));
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            $code = Str::slug($name);
            if ($code === '') {
                $code = 's-' . Str::lower(Str::random(5));
            }
        }
        $code = Str::limit($code, 30, '');

        // Resolve collision
        $originalCode = $code;
        $counter = 1;
        while (Shift::where('code', $code)->exists()) {
            $suffix = '-' . $counter;
            $code = Str::limit($originalCode, 30 - strlen($suffix), '') . $suffix;
            $counter++;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:shift,code',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 255 karakter.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
        ]);

        if ($validated['starts_at'] === $validated['ends_at']) {
            return back()->withErrors(['ends_at' => 'Jam selesai tidak boleh sama persis dengan jam mulai.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        Shift::create($validated);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil ditambahkan.');
    }

    /**
     * Update shift kerja.
     */
    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            $code = $shift->code;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:30|unique:shift,code,' . $shift->id,
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 255 karakter.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
        ]);

        if ($validated['starts_at'] === $validated['ends_at']) {
            return back()->withErrors(['ends_at' => 'Jam selesai tidak boleh sama persis dengan jam mulai.'])->withInput();
        }

        // Jika data sudah dirujuk aktivitas, kunci perubahan nama agar riwayat tetap konsisten
        if ($shift->dailyInspections()->exists() && $shift->name !== $validated['name']) {
            return back()->withErrors([
                'name' => 'Nama shift yang sudah dirujuk inspeksi tidak dapat diganti namanya agar riwayat tetap konsisten.'
            ])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $shift->update($validated);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil diperbarui.');
    }

    /**
     * Hapus shift kerja. Tolak jika masih dirujuk daily_inspections.
     */
    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->dailyInspections()->exists()) {
            return redirect()->route('shifts.index')
                ->with('error', 'Shift kerja tidak dapat dihapus karena masih terkait dengan data inspeksi harian.');
        }

        $shift->delete();

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil dihapus.');
    }
}

