<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkAreaController extends Controller
{
    public function index(Request $request): View
    {
        $workAreas = WorkArea::query()
            ->with('department:id,name')
            ->withCount('questions')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->when($request->status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('work-areas.index', [
            'workAreas' => $workAreas,
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkArea($request);
        $validated['code'] = $validated['code'] ?: Str::upper(Str::limit(Str::slug($validated['name'], ''), 30, ''));
        $validated['is_active'] = $request->boolean('is_active', true);

        $workArea = WorkArea::create($validated);
        $this->audit($request, 'work_area.created', $workArea, null, $workArea->toArray());

        return redirect()->route('admin.work-areas.index')->with('success', 'Area kerja berhasil ditambahkan.');
    }

    public function show(WorkArea $workArea): View
    {
        $workArea->load('department:id,name');
        $questions = $workArea->questions()->orderBy('sort_order')->orderBy('id')->paginate(15);

        return view('work-areas.show', compact('workArea', 'questions'));
    }

    public function update(Request $request, WorkArea $workArea): RedirectResponse
    {
        $before = $workArea->toArray();
        $validated = $this->validateWorkArea($request, $workArea);
        $validated['code'] = $validated['code'] ?: Str::upper(Str::limit(Str::slug($validated['name'], ''), 30, ''));
        $validated['is_active'] = $request->boolean('is_active');
        $workArea->update($validated);
        $this->audit($request, 'work_area.updated', $workArea, $before, $workArea->fresh()->toArray());

        return back()->with('success', 'Area kerja berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, WorkArea $workArea): RedirectResponse
    {
        $before = $workArea->toArray();
        $workArea->update(['is_active' => ! $workArea->is_active]);
        $this->audit($request, 'work_area.status_changed', $workArea, $before, $workArea->fresh()->toArray());

        return back()->with('success', 'Status area kerja berhasil diperbarui.');
    }

    private function validateWorkArea(Request $request, ?WorkArea $workArea = null): array
    {
        return $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('work_areas', 'name')->ignore($workArea)],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('work_areas', 'code')->ignore($workArea)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'department_id.required' => 'Departemen wajib dipilih.',
            'name.required' => 'Nama area kerja wajib diisi.',
            'name.unique' => 'Nama area kerja sudah digunakan.',
            'code.unique' => 'Kode area kerja sudah digunakan.',
        ]);
    }

    private function audit(Request $request, string $action, WorkArea $workArea, ?array $before, array $after): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => WorkArea::class,
            'entity_id' => $workArea->id,
            'before_values' => $before ? json_encode($before) : null,
            'after_values' => json_encode($after),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
