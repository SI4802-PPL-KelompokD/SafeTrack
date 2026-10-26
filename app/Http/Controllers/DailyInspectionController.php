<?php

namespace App\Http\Controllers;

use App\Models\DailyInspection;
use App\Models\InspectionQuestion;
use App\Models\Shift;
use App\Models\WorkArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DailyInspectionController extends Controller
{
    public function index(Request $request): Response
    {
        $isAdmin = $request->user()->can('manage-administration');
        $inspections = DailyInspection::query()
            ->with(['inspector:id,name', 'workArea:id,name', 'shift:id,name'])
            ->when(! $isAdmin, fn ($query) => $query->where('inspector_id', $request->user()->id))
            ->latest('inspection_date')
            ->latest('id')
            ->paginate(10);

        return $this->inspectionView($request, $inspections, false);
    }

    public function create(Request $request): Response
    {
        $inspections = DailyInspection::query()
            ->with(['inspector:id,name', 'workArea:id,name', 'shift:id,name'])
            ->where('inspector_id', $request->user()->id)
            ->latest('inspection_date')
            ->latest('id')
            ->paginate(10);

        return $this->inspectionView($request, $inspections, true);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'work_area_id' => ['required', Rule::exists('work_areas', 'id')->where('is_active', true)],
            'shift_id' => ['required', Rule::exists('shifts', 'id')->where('is_active', true)],
            'inspection_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer' => ['required', Rule::in(['yes', 'no'])],
            'answers.*.description' => ['nullable', 'required_if:answers.*.answer,no', 'string', 'max:2000'],
            'answers.*.photo' => ['nullable', 'required_if:answers.*.answer,no', 'image', 'max:5120'],
        ], [
            'work_area_id.required' => 'Area kerja wajib dipilih.',
            'work_area_id.exists' => 'Area kerja tidak aktif atau tidak tersedia.',
            'answers.required' => 'Jawaban inspeksi wajib diisi.',
            'answers.*.description.required_if' => 'Deskripsi kondisi wajib diisi untuk jawaban Tidak.',
            'answers.*.photo.required_if' => 'Foto kondisi wajib diunggah untuk jawaban Tidak.',
        ]);

        $questions = InspectionQuestion::query()
            ->where('work_area_id', $validated['work_area_id'])
            ->activeOrdered()
            ->get()
            ->keyBy('id');
        $submittedIds = collect($validated['answers'])->pluck('question_id');

        if ($questions->isEmpty() || $submittedIds->duplicates()->isNotEmpty() || $submittedIds->sort()->values()->all() !== $questions->keys()->sort()->values()->all()) {
            throw ValidationException::withMessages(['answers' => 'Daftar pertanyaan tidak sesuai dengan area kerja yang dipilih.']);
        }

        DB::transaction(function () use ($request, $validated, $questions): void {
            $inspection = DailyInspection::create([
                'client_request_id' => Str::uuid(),
                'inspector_id' => $request->user()->id,
                'department_id' => $request->user()->department_id,
                'work_area_id' => $validated['work_area_id'],
                'shift_id' => $validated['shift_id'],
                'inspection_date' => $validated['inspection_date'],
                'status' => 'submitted',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['answers'] as $index => $answerData) {
                $question = $questions->get($answerData['question_id']);
                $answer = $inspection->answers()->create([
                    'inspection_question_id' => $question->id,
                    'question_snapshot' => $question->question,
                    'answer' => $answerData['answer'],
                    'notes' => $answerData['description'] ?? null,
                ]);

                if ($request->hasFile("answers.$index.photo")) {
                    $photo = $request->file("answers.$index.photo");
                    $path = $photo->store('inspection-photos', 'local');
                    DB::table('inspection_photos')->insert([
                        'inspection_answer_id' => $answer->id,
                        'uploaded_by' => $request->user()->id,
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('daily-inspections.index')->with('success', 'Inspeksi harian berhasil disimpan.');
    }

    private function inspectionView(Request $request, LengthAwarePaginator $inspections, bool $openCreateModal): Response
    {
        return Inertia::render('DailyInspections/Index', [
            'inspections' => $inspections,
            'isAdmin' => $request->user()->can('manage-administration'),
            'openCreateModal' => $openCreateModal || $request->session()->has('errors'),
            'workAreas' => WorkArea::query()->active()->orderBy('name')->get(['id', 'name']),
            'shifts' => Shift::query()->where('is_active', true)->orderBy('code')->get(['id', 'name']),
            'endpoints' => [
                'index' => route('daily-inspections.index'),
                'create' => route('daily-inspections.create'),
                'store' => route('daily-inspections.store'),
                'questions' => route('work-areas.active-questions', ['workArea' => '__AREA__']),
            ],
        ]);
    }
}
