<?php

namespace App\Http\Controllers;

use App\Models\SapMonthlySummary;
use App\Models\SapTarget;
use App\Models\User;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SapMonitoringController extends Controller
{
    public const DEFAULT_HAZARD_TARGET = 4;

    public const DEFAULT_INSPECTION_TARGET = 3;

    /**
     * Halaman Monitoring Pencapaian SAP (PBI-10 / UC-24).
     * Tampilan dibedakan menurut role: admin, hse, atau employee (non-HSE).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $mode = $this->resolveViewMode($user);

        $period = $this->resolvePeriod($request, $mode);
        $periodDate = $period->format('Y-m-01');

        $data = match ($mode) {
            'admin' => $this->adminData($periodDate),
            'hse' => $this->hseData($user, $periodDate),
            default => $this->employeeData($user, $periodDate),
        };

        return view('sap.monitoring', array_merge([
            'title' => 'Pencapaian SAP',
            'pageTitle' => 'Pencapaian SAP',
            'mode' => $mode,
            'heading' => $this->headingFor($mode),
            'description' => $this->descriptionFor($mode),
            'period' => $period,
            'defaultHazardTarget' => self::DEFAULT_HAZARD_TARGET,
            'defaultInspectionTarget' => self::DEFAULT_INSPECTION_TARGET,
        ], $data));
    }

    /**
     * Admin dicek lebih dulu, lalu Karyawan HSE (departemen is_hse), sisanya Non-HSE.
     */
    protected function resolveViewMode(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->department?->is_hse) {
            return 'hse';
        }

        return 'employee';
    }

    /**
     * Hanya Admin yang boleh memilih periode (format Y-m). Role lain selalu bulan berjalan.
     */
    protected function resolvePeriod(Request $request, string $mode): Carbon
    {
        if ($mode === 'admin') {
            $validated = $request->validate([
                'period' => ['nullable', 'date_format:Y-m'],
            ]);

            if (! empty($validated['period'])) {
                return Carbon::createFromFormat('!Y-m', $validated['period']);
            }
        }

        return Carbon::now()->startOfMonth();
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminData(string $periodDate): array
    {
        $hseEmployees = User::query()
            ->with('department')
            ->where('is_active', true)
            ->whereNotIn('role', ['super_admin', 'admin'])
            ->whereHas('department', fn ($query) => $query->where('is_hse', true))
            ->orderBy('name')
            ->get();

        $summaries = SapMonthlySummary::query()
            ->where('period', $periodDate)
            ->whereIn('user_id', $hseEmployees->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $targets = $this->targetsFor($hseEmployees->pluck('id'), $periodDate);

        $hseRows = $hseEmployees->map(fn (User $employee) => [
            'user' => $employee,
            'achievement' => $this->buildAchievement($summaries->get($employee->id), $targets[$employee->id]),
        ]);

        $topContributors = SapMonthlySummary::query()
            ->with('user')
            ->where('period', $periodDate)
            ->whereHas('user', fn ($query) => $query->whereNotIn('role', ['super_admin', 'admin']))
            ->orderByDesc('total_points')
            ->orderBy('user_id')
            ->limit(3)
            ->get();

        return [
            'hseRows' => $hseRows,
            'topContributors' => $topContributors,
            'hasSummaryData' => $summaries->isNotEmpty(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function hseData(User $user, string $periodDate): array
    {
        $summary = $this->summaryFor($user, $periodDate);
        $targets = $this->targetsFor(collect([$user->id]), $periodDate);

        return [
            'achievement' => $this->buildAchievement($summary, $targets[$user->id]),
            'hasSummaryData' => $summary !== null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function employeeData(User $user, string $periodDate): array
    {
        $summary = $this->summaryFor($user, $periodDate);

        $hazard = (int) ($summary?->hazard_count ?? 0);
        $inspection = (int) ($summary?->inspection_count ?? 0);
        $safetyTalk = (int) ($summary?->safety_talk_count ?? 0);

        return [
            'contribution' => [
                'hazard' => $hazard,
                'inspection' => $inspection,
                'safety_talk' => $safetyTalk,
                'total' => $hazard + $inspection + $safetyTalk,
            ],
        ];
    }

    protected function summaryFor(User $user, string $periodDate): ?SapMonthlySummary
    {
        return SapMonthlySummary::query()
            ->where('user_id', $user->id)
            ->where('period', $periodDate)
            ->first();
    }

    /**
     * Target bulanan per karyawan: nilai dari tabel target jika ada, jika tidak memakai default.
     *
     * @param  Collection<int, int>  $userIds
     * @return array<int, array{hazard: int, inspection: int}>
     */
    protected function targetsFor(Collection $userIds, string $periodDate): array
    {
        $customTargets = SapTarget::query()
            ->where('period', $periodDate)
            ->whereIn('user_id', $userIds)
            ->whereIn('activity', [
                SapCalculationService::ACTIVITY_HAZARD,
                SapCalculationService::ACTIVITY_INSPECTION,
            ])
            ->get()
            ->groupBy('user_id');

        $targets = [];

        foreach ($userIds as $userId) {
            $userTargets = $customTargets->get($userId, collect())->pluck('target_count', 'activity');

            $targets[$userId] = [
                'hazard' => (int) ($userTargets[SapCalculationService::ACTIVITY_HAZARD] ?? self::DEFAULT_HAZARD_TARGET),
                'inspection' => (int) ($userTargets[SapCalculationService::ACTIVITY_INSPECTION] ?? self::DEFAULT_INSPECTION_TARGET),
            ];
        }

        return $targets;
    }

    /**
     * Susun angka realisasi, target, sisa target, dan persentase pencapaian.
     *
     * @param  array{hazard: int, inspection: int}  $targets
     * @return array<string, int>
     */
    protected function buildAchievement(?SapMonthlySummary $summary, array $targets): array
    {
        $hazard = (int) ($summary?->hazard_count ?? 0);
        $inspection = (int) ($summary?->inspection_count ?? 0);

        $targetTotal = $targets['hazard'] + $targets['inspection'];
        $achievedTotal = min($hazard, $targets['hazard']) + min($inspection, $targets['inspection']);

        return [
            'hazard' => $hazard,
            'hazard_target' => $targets['hazard'],
            'hazard_remaining' => max($targets['hazard'] - $hazard, 0),
            'inspection' => $inspection,
            'inspection_target' => $targets['inspection'],
            'inspection_remaining' => max($targets['inspection'] - $inspection, 0),
            'safety_talk' => (int) ($summary?->safety_talk_count ?? 0),
            'percentage' => $targetTotal > 0 ? (int) round($achievedTotal / $targetTotal * 100) : 0,
        ];
    }

    protected function headingFor(string $mode): string
    {
        return match ($mode) {
            'admin' => 'Target HSE & Top 3 Karyawan',
            'hse' => 'Target bulanan HSE',
            default => 'Kontribusi keselamatan',
        };
    }

    protected function descriptionFor(string $mode): string
    {
        return match ($mode) {
            'admin' => 'Admin memantau pencapaian tanpa memiliki target aktivitas pribadi.',
            default => 'Pantau aktivitas keselamatan pada bulan berjalan.',
        };
    }
}
