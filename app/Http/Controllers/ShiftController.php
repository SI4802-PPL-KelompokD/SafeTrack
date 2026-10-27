<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Tampilkan daftar shift kerja.
     */
    public function index(): View
    {
        $shifts = Shift::orderBy('code')->get();

        return view('shifts.index', compact('shifts'));
    }

    /**
     * Simpan shift kerja baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:shift,code',
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Kode shift wajib diisi.',
            'code.max' => 'Kode shift maksimal 30 karakter.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 255 karakter.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'ends_at.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Shift::create($validated);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil ditambahkan.');
    }

    /**
     * Update shift kerja.
     */
    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:shift,code,' . $shift->id,
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'is_active' => 'boolean',
        ], [
            'code.required' => 'Kode shift wajib diisi.',
            'code.max' => 'Kode shift maksimal 30 karakter.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 255 karakter.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
            'ends_at.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

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
