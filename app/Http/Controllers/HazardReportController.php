<?php

namespace App\Http\Controllers;

use App\Http\Requests\HazardReportFilterRequest;
use App\Models\Department;
use App\Models\HazardReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
}
