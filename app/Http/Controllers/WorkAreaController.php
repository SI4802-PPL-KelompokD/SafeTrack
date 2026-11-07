<?php

namespace App\Http\Controllers;

use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkAreaController extends Controller
{
    public function index(Request $request): Response
    {
        $workAreas = WorkArea::query()
            ->withCount('questions')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('WorkAreas/Index', [
            'workAreas' => $workAreas,
            'filters' => $request->only(['search']),
            'endpoints' => [
                'index' => route('admin.work-areas.index'),
                'store' => route('admin.work-areas.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWorkArea($request, withQuestions: true);
        $workArea = DB::transaction(function () use ($validated): WorkArea {
            $workArea = WorkArea::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            foreach ($validated['questions'] as $index => $question) {
                $workArea->questions()->create([
                    'question' => $question,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            return $workArea;
        });
        $this->audit($request, 'work_area.created', $workArea, null, $workArea->toArray());

        return redirect()->route('admin.work-areas.index')->with('success', 'Area kerja berhasil ditambahkan.');
    }

    public function show(WorkArea $workArea): Response
    {
        $questions = $workArea->questions()->orderBy('sort_order')->orderBy('id')->paginate(15);

        return Inertia::render('WorkAreas/Show', [
            'workArea' => $workArea,
            'questions' => $questions,
            'endpoints' => [
                'index' => route('admin.work-areas.index'),
                'update' => route('admin.work-areas.update', $workArea),
                'questionsStore' => route('admin.work-areas.questions.store', $workArea),
            ],
        ]);
    }

    public function update(Request $request, WorkArea $workArea): RedirectResponse
    {
        $before = $workArea->toArray();
        $validated = $this->validateWorkArea($request, $workArea);
        $workArea->update($validated);
        $this->audit($request, 'work_area.updated', $workArea, $before, $workArea->fresh()->toArray());

        return back()->with('success', 'Area kerja berhasil diperbarui.');
    }

    public function destroy(Request $request, WorkArea $workArea): RedirectResponse
    {
        $before = $workArea->toArray();
        $workArea->delete();
        $this->audit($request, 'work_area.deleted', $workArea, $before, []);

        return redirect()->route('admin.work-areas.index')->with('success', 'Area kerja berhasil dihapus.');
    }

    public function toggleStatus(Request $request, WorkArea $workArea): RedirectResponse
    {
        $before = $workArea->toArray();
        $workArea->update(['is_active' => ! $workArea->is_active]);
        $this->audit($request, 'work_area.status_changed', $workArea, $before, $workArea->fresh()->toArray());

        return back()->with('success', 'Status area kerja berhasil diperbarui.');
    }

    private function validateWorkArea(Request $request, ?WorkArea $workArea = null, bool $withQuestions = false): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('area_kerja', 'name')->ignore($workArea)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];

        if ($withQuestions) {
            $rules['questions'] = ['required', 'array', 'size:3'];
            $rules['questions.*'] = ['required', 'string', 'max:2000', 'distinct'];
        }

        return $request->validate($rules, [
            'name.required' => 'Nama area kerja wajib diisi.',
            'name.unique' => 'Nama area kerja sudah digunakan.',
            'questions.size' => 'Tiga pertanyaan awal wajib diisi.',
            'questions.*.required' => 'Semua pertanyaan awal wajib diisi.',
            'questions.*.distinct' => 'Pertanyaan awal tidak boleh sama.',
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
