<?php

namespace App\Http\Controllers;

use App\Models\InspectionQuestion;
use App\Models\WorkArea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InspectionQuestionController extends Controller
{
    public function index(WorkArea $workArea): JsonResponse
    {
        abort_unless($workArea->is_active, 404);

        return response()->json([
            'data' => $workArea->questions()->activeOrdered()->get(['id', 'question', 'sort_order']),
        ]);
    }

    public function store(Request $request, WorkArea $workArea): RedirectResponse
    {
        $validated = $this->validateQuestion($request, $workArea);
        $validated['is_active'] = $request->boolean('is_active', true);
        $question = $workArea->questions()->create($validated);
        $this->audit($request, 'inspection_question.created', $question, null, $question->toArray());

        return back()->with('success', 'Pertanyaan inspeksi berhasil ditambahkan.');
    }

    public function update(Request $request, WorkArea $workArea, InspectionQuestion $question): RedirectResponse
    {
        $this->ensureOwner($workArea, $question);
        $before = $question->toArray();
        $validated = $this->validateQuestion($request, $workArea, $question);
        $validated['is_active'] = $request->boolean('is_active');
        $question->update($validated);
        $action = $before['sort_order'] !== $question->sort_order
            ? 'inspection_question.order_changed'
            : 'inspection_question.updated';
        $this->audit($request, $action, $question, $before, $question->fresh()->toArray());

        return back()->with('success', 'Pertanyaan inspeksi berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, WorkArea $workArea, InspectionQuestion $question): RedirectResponse
    {
        $this->ensureOwner($workArea, $question);
        $before = $question->toArray();
        $question->update(['is_active' => ! $question->is_active]);
        $this->audit($request, 'inspection_question.status_changed', $question, $before, $question->fresh()->toArray());

        return back()->with('success', 'Status pertanyaan berhasil diperbarui.');
    }

    public function destroy(Request $request, WorkArea $workArea, InspectionQuestion $question): RedirectResponse
    {
        $this->ensureOwner($workArea, $question);

        if (DB::table('inspection_answers')->where('inspection_question_id', $question->id)->exists()) {
            $question->update(['is_active' => false]);

            return back()->with('error', 'Pertanyaan sudah pernah digunakan dan hanya dapat dinonaktifkan.');
        }

        $before = $question->toArray();
        $question->delete();
        $this->audit($request, 'inspection_question.deleted', $question, $before, []);

        return back()->with('success', 'Pertanyaan inspeksi berhasil dihapus.');
    }

    private function validateQuestion(Request $request, WorkArea $workArea, ?InspectionQuestion $question = null): array
    {
        return $request->validate([
            'question' => [
                'required',
                'string',
                'max:2000',
                Rule::unique('inspection_questions', 'question')->where('work_area_id', $workArea->id)->ignore($question),
            ],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'question.required' => 'Teks pertanyaan wajib diisi.',
            'question.unique' => 'Pertanyaan yang sama sudah tersedia pada area ini.',
            'sort_order.required' => 'Urutan tampil wajib diisi.',
            'sort_order.integer' => 'Urutan tampil harus berupa angka.',
            'sort_order.min' => 'Urutan tampil minimal 1.',
        ]);
    }

    private function ensureOwner(WorkArea $workArea, InspectionQuestion $question): void
    {
        abort_unless($question->work_area_id === $workArea->id, 404);
    }

    private function audit(Request $request, string $action, InspectionQuestion $question, ?array $before, array $after): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => InspectionQuestion::class,
            'entity_id' => $question->id,
            'before_values' => $before ? json_encode($before) : null,
            'after_values' => json_encode($after),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
