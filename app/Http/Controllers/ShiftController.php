<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Shift;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(Request $request): View
    {
        $shifts = Shift::query()
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->orderByDesc('is_active')
            ->orderBy('starts_at')
            ->orderBy('id')
            ->paginate(5)
            ->withQueryString();

        return view('shifts.index', [
            'shifts' => $shifts,
            'tabCounts' => [
                'departments' => Department::count(),
                'shifts' => Shift::count(),
                'work_areas' => WorkArea::count(),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        $request->merge(['name' => $name]);

        $code = Str::slug($name);
        if ($code === '') {
            $code = 's-'.Str::lower(Str::random(5));
        }
        $code = Str::limit($code, 30, '');

        $originalCode = $code;
        $counter = 1;
        while (Shift::where('code', $code)->exists()) {
            $suffix = '-'.$counter;
            $code = Str::limit($originalCode, 30 - strlen($suffix), '').$suffix;
            $counter++;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('shift', 'name')],
            'code' => ['required', 'string', 'max:30', Rule::unique('shift', 'code')],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
        ], [
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 50 karakter.',
            'name.unique' => 'Nama shift sudah ada.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
        ]);

        if ($validated['starts_at'] === $validated['ends_at']) {
            return back()
                ->withErrors(['ends_at' => 'Jam selesai tidak boleh sama dengan jam mulai.'])
                ->withInput();
        }

        $validated['is_active'] = true;

        $shift = Shift::create($validated);
        $this->audit($request, 'shift.created', $shift, null, $shift->toArray());

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil ditambahkan.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        $request->merge(['name' => $name]);

        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            $code = $shift->code;
        }
        $request->merge(['code' => $code]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('shift', 'name')->ignore($shift->id)],
            'code' => ['required', 'string', 'max:30', Rule::unique('shift', 'code')->ignore($shift->id)],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
        ], [
            'name.required' => 'Nama shift wajib diisi.',
            'name.max' => 'Nama shift maksimal 50 karakter.',
            'name.unique' => 'Nama shift sudah ada.',
            'code.unique' => 'Kode shift sudah digunakan.',
            'starts_at.required' => 'Jam mulai wajib diisi.',
            'starts_at.date_format' => 'Format jam mulai tidak valid (HH:MM).',
            'ends_at.required' => 'Jam selesai wajib diisi.',
            'ends_at.date_format' => 'Format jam selesai tidak valid (HH:MM).',
        ]);

        if ($validated['starts_at'] === $validated['ends_at']) {
            return back()
                ->withErrors(['ends_at' => 'Jam selesai tidak boleh sama dengan jam mulai.'])
                ->withInput();
        }

        $before = $shift->toArray();
        $shift->update($validated);
        $this->audit($request, 'shift.updated', $shift, $before, $shift->fresh()->toArray());

        return redirect()->route('shifts.index')
            ->with('success', 'Shift kerja berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, Shift $shift): RedirectResponse
    {
        $request->validate(['is_active' => ['required', 'boolean']]);
        $before = $shift->toArray();
        $shift->update(['is_active' => $request->boolean('is_active')]);
        $this->audit($request, 'shift.status_changed', $shift, $before, $shift->fresh()->toArray());

        return back()->with('success', 'Status shift kerja berhasil diperbarui.');
    }

    private function audit(Request $request, string $action, Shift $shift, ?array $before, array $after): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => Shift::class,
            'entity_id' => $shift->id,
            'before_values' => $before ? json_encode($before) : null,
            'after_values' => json_encode([...$after, 'admin_name' => $request->input('admin_name'), 'action_reason' => $request->input('action_reason')]),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
