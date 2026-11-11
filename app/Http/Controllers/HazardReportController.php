<?php

namespace App\Http\Controllers;

use App\Http\Requests\HazardReportFilterRequest;
use App\Models\Department;
use App\Models\HazardReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreHazardReportRequest;
use App\Models\HazardCategory;
use App\Models\HazardPhoto;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PBI-13 — Laporan Saya, Daftar Pelaporan, dan Detail Laporan (read-only).
 */
class HazardReportController extends Controller
{
    /** Daftar Pelaporan: admin = semua; karyawan = miliknya + laporan terverifikasi. */
    public function index(HazardReportFilterRequest $request): View
    {
        Gate::authorize('viewAny', HazardReport::class);

        $user = $request->user();
        $archive = $user->isAdmin() && $request->boolean('archive');

        $reports = $this->baseQuery()
            ->visibleTo($user)
            ->when($archive, fn ($q) => $q->onlyTrashed())
            ->filter($request->validated())
            ->latest('reported_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('hazards.index', $this->viewData($request, $reports, 'all', $archive));
    }

    /** Laporan Saya: semua laporan milik pengguna login, semua status. */
    public function mine(HazardReportFilterRequest $request): View
    {
        Gate::authorize('viewAny', HazardReport::class);

        $reports = $this->baseQuery()
            ->where('reporter_id', $request->user()->id)
            ->filter($request->validated())
            ->latest('reported_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('hazards.index', $this->viewData($request, $reports, 'mine', false));
    }

    /** Detail Laporan. Policy mengembalikan 404 untuk laporan yang tidak boleh dilihat. */
    public function show(Request $request, HazardReport $hazardReport): View
    {
        Gate::authorize('view', $hazardReport);

        $hazardReport->load([
            'reporter:id,name,department_id',
            'reporterDepartment:id,name',
            'repairDepartment:id,name',
            'category:id,name',
            'workArea:id,name',
            'repairPic:id,name',
            'assignedAdmin:id,name',
            'verifier:id,name',
            'photos',
            'histories.actor:id,name',
            'resolutions' => fn ($q) => $q->latest('submitted_at')->latest('id'),
            'resolutions.photos',
            'resolutions.submitter:id,name',
            'resolutions.reviewer:id,name',
        ]);

        return view('hazards.show', [
            'report' => $hazardReport,
            'isAdmin' => $request->user()->isAdmin(),
            'isOwner' => $hazardReport->reporter_id === $request->user()->id,
        ]);
    }

    private function baseQuery()
    {
        return HazardReport::query()->with([
            'reporter:id,name',
            'reporterDepartment:id,name',
            'repairPic:id,name',
        ]);
    }

    private function viewData(Request $request, $reports, string $mode, bool $archive): array
    {
        return [
            'reports' => $reports,
            'mode' => $mode,
            'archive' => $archive,
            'isAdmin' => $request->user()->isAdmin(),
            'filters' => $request->validated(),
            'statuses' => HazardReport::STATUS_LABELS,
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    /** PBI-11 — Menampilkan form pembuatan laporan bahaya. */
    public function create(): Response
    {
        return Inertia::render('Hazards/Create', [
            'categories' => HazardCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),

            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),

            'endpoints' => [
                'store' => route('hazards.store'),
                'create' => route('hazards.create'),
            ],
        ]);
    }

    /** PBI-11 — Menyimpan laporan bahaya baru. */
    public function store(
        StoreHazardReportRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $validated = $request->validated();

        // Gunakan UUID dari frontend atau buat UUID baru.
        $clientRequestId = $validated['client_request_id']
            ?? (string) Str::uuid();

        $storedFiles = [];

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $auditLogger,
                $clientRequestId,
                &$storedFiles
            ) {
                // Mencegah laporan ganda berdasarkan client_request_id.
                $report = HazardReport::firstOrCreate(
                    ['client_request_id' => $clientRequestId],
                    [
                        'reporter_id' => $request->user()->id,
                        'title' => $validated['title'],
                        'location' => $validated['location'],
                        'description' => $validated['description'],
                        'category_id' => $validated['category_id'],
                        'reporter_department_id' => $validated['reporter_department_id'],
                        'repair_department_id' => $validated['repair_department_id'],
                        'immediate_action' => $validated['immediate_action'],

                        // TODO: Menunggu keputusan tim mengenai risk_level.
                        'risk_level' => 'medium',

                        'status' => HazardReport::STATUS_OPEN,

                        // Menggunakan waktu server.
                        'reported_at' => now(),
                    ]
                );

                // Jika UUID sudah pernah digunakan, tidak membuat data baru.
                if (! $report->wasRecentlyCreated) {
                    return;
                }

                // Menyimpan foto secara privat menggunakan disk local.
                foreach ($request->file('photos', []) as $photo) {
                    $path = $photo->store(
                        'hazard-reports',
                        'local'
                    );

                    if (! $path) {
                        throw new \RuntimeException(
                            'Foto bukti gagal disimpan.'
                        );
                    }

                    $storedFiles[] = $path;

                    // Menyimpan informasi foto ke tabel hazard_photos.
                    HazardPhoto::create([
                        'hazard_report_id' => $report->id,
                        'uploaded_by' => $request->user()->id,
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'mime_type' => $photo->getMimeType(),
                        'size_bytes' => $photo->getSize(),
                    ]);
                }

                // Mencatat aktivitas pembuatan laporan ke audit log.
                $auditLogger->log(
                    $request->user(),
                    'tambah',
                    $report,
                    null,
                    $report->fresh()->toArray()
                );
            });
        } catch (\Throwable $exception) {
            // Menghapus file dari disk local jika transaksi gagal.
            if (! empty($storedFiles)) {
                Storage::disk('local')->delete($storedFiles);
            }

            throw $exception;
        }

        return redirect()
            ->route('hazards.create')
            ->with('success', 'Laporan bahaya berhasil dibuat.');
    }
}