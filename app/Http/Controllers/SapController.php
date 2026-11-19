<?php

namespace App\Http\Controllers;

use App\Models\SapMonthlySummary;
use App\Models\SapTarget;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class SapController extends Controller
{
    protected SapCalculationService $sapService;

    public function __construct(SapCalculationService $sapService)
    {
        $this->sapService = $sapService;
    }

    /**
     * Tampilkan halaman Monitoring Pencapaian SAP Bulanan.
     * Khusus Admin.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'required', 'integer', 'between:2000,2050'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $selectedYear = (int) ($validated['year'] ?? Carbon::now()->year);
        $selectedMonth = (int) ($validated['month'] ?? Carbon::now()->month);

        $periodCarbon = Carbon::createFromDate(
            $selectedYear,
            $selectedMonth,
            1
        )->startOfMonth();

        $periodDate = $periodCarbon->format('Y-m-01');
        $search = trim($validated['search'] ?? '');

        $summaries = collect();
        $hseRows = collect();
        $hasTargetData = false;
        $dataError = false;

        $totalPoints = 0;
        $participatingEmployees = 0;
        $totalEmployeesRecapped = 0;
        $targetMetCount = 0;
        $rewardEligibleCount = 0;
        $averagePoints = 0;

        try {
            /*
             * Filter pencarian karyawan berdasarkan:
             * - Nama
             * - Username
             * - NIK
             * - Nama lengkap pada employee registry
             */
            $searchUsers = static function ($query) use ($search): void {
                if ($search === '') {
                    return;
                }

                $query->where(function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhereHas('employeeRegistry', function ($registryQuery) use ($search): void {
                            $registryQuery->where('nik', 'like', "%{$search}%")
                                ->orWhere('full_name', 'like', "%{$search}%");
                        });
                });
            };

            /*
             * TOP 3 KARYAWAN NON-HSE
             * Hanya menampilkan tiga karyawan dengan poin tertinggi.
             */
            $summaries = SapMonthlySummary::query()
                ->with([
                    'user.employeeRegistry',
                    'department',
                    'eligibleReward',
                ])
                ->where('period', $periodDate)
                ->where('is_hse_snapshot', false)
                ->whereHas('user', function ($query) use ($searchUsers): void {
                    $query->where('role', 'employee')
                        ->whereHas('department', fn ($departmentQuery) =>
                            $departmentQuery->where('is_hse', false)
                        );

                    $searchUsers($query);
                })
                ->orderByDesc('total_points')
                ->orderBy('user_id')
                ->limit(3)
                ->get();

            /*
             * TARGET AKTIVITAS HSE
             */
            $targetRecords = SapTarget::query()
                ->where('period', $periodDate)
                ->get()
                ->groupBy('user_id')
                ->map(
                    fn (Collection $targets): Collection =>
                        $targets->keyBy('activity')
                );

            $hasTargetData = $targetRecords->isNotEmpty();

            $activityTargets = [
                'hazard' => SapCalculationService::ACTIVITY_HAZARD,
                'inspection' => SapCalculationService::ACTIVITY_INSPECTION,
                'safety_talk' => SapCalculationService::ACTIVITY_SAFETY_TALK,
            ];

            /*
             * DATA PENCAPAIAN KARYAWAN HSE
             */
            $hseSummaries = SapMonthlySummary::query()
                ->with([
                    'user.employeeRegistry',
                    'department',
                ])
                ->where('period', $periodDate)
                ->where('is_hse_snapshot', true)
                ->whereHas('user', function ($query) use ($searchUsers): void {
                    $query->where('role', 'employee')
                        ->whereHas('department', fn ($departmentQuery) =>
                            $departmentQuery->where('is_hse', true)
                        );

                    $searchUsers($query);
                })
                ->orderBy('user_id')
                ->paginate(15)
                ->withQueryString();

            /*
             * Ubah setiap data HSE menjadi format yang dibutuhkan Blade.
             */
            $hseRows = $hseSummaries->through(
                function (SapMonthlySummary $summary) use (
                    $activityTargets,
                    $targetRecords
                ): array {
                    $userTargets = $targetRecords->get(
                        $summary->user_id,
                        collect()
                    );

                    $activities = [];

                    foreach ($activityTargets as $key => $activity) {
                        $target = $userTargets->get($activity)?->target_count;

                        $actual = (int) $summary->{match ($key) {
                            'hazard' => 'hazard_count',
                            'inspection' => 'inspection_count',
                            'safety_talk' => 'safety_talk_count',
                        }};

                        $activities[$key] = [
                            'actual' => $actual,
                            'target' => $target,
                            'percentage' => $target !== null && $target > 0
                                ? round(($actual / $target) * 100, 1)
                                : null,
                        ];
                    }

                    return [
                        'name' => $summary->user?->name ?? 'Karyawan',
                        'department' => $summary->department?->name ?? '-',
                        'hazard' => $activities['hazard'],
                        'inspection' => $activities['inspection'],
                        'safety_talk' => $activities['safety_talk'],
                    ];
                }
            );

            /*
             * STATISTIK REKAP SAP BULANAN
             */
            $baseStatsQuery = SapMonthlySummary::query()
                ->where('period', $periodDate);

            $totalPoints = (int) (clone $baseStatsQuery)
                ->sum('total_points');

            $participatingEmployees = (int) (clone $baseStatsQuery)
                ->where('total_points', '>', 0)
                ->count();

            $totalEmployeesRecapped = (int) (clone $baseStatsQuery)
                ->count();

            $targetMetCount = (int) (clone $baseStatsQuery)
                ->where('target_met', true)
                ->count();

            $rewardEligibleCount = (int) (clone $baseStatsQuery)
                ->where('reward_eligible', true)
                ->count();

            $averagePoints = $totalEmployeesRecapped > 0
                ? round($totalPoints / $totalEmployeesRecapped, 1)
                : 0;

        } catch (Throwable $exception) {
            Log::error('Gagal memuat monitoring pencapaian SAP.', [
                'period' => $periodDate,
                'exception' => $exception,
            ]);

            $summaries = collect();
            $hseRows = collect();
            $hasTargetData = false;
            $dataError = true;

            $totalPoints = 0;
            $participatingEmployees = 0;
            $totalEmployeesRecapped = 0;
            $targetMetCount = 0;
            $rewardEligibleCount = 0;
            $averagePoints = 0;
        }

        return view('sap.index', [
            'summaries' => $summaries,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'periodCarbon' => $periodCarbon,
            'periodDate' => $periodDate,
            'totalPoints' => $totalPoints,
            'participatingEmployees' => $participatingEmployees,
            'totalEmployeesRecapped' => $totalEmployeesRecapped,
            'targetMetCount' => $targetMetCount,
            'rewardEligibleCount' => $rewardEligibleCount,
            'averagePoints' => $averagePoints,
            'hseRows' => $hseRows,
            'hasTargetData' => $hasTargetData,
            'dataError' => $dataError,
            'search' => $search,
        ]);
    }

    /**
     * Eksekusi hitung ulang SAP bulanan oleh Admin.
     */
    public function recalculate(Request $request)
    {
        $validated = $request->validate([
            'month' => ['sometimes', 'required', 'integer', 'between:1,12'],
            'year' => ['sometimes', 'required', 'integer', 'between:2000,2050'],
        ]);

        $selectedYear = (int) ($validated['year'] ?? Carbon::now()->year);
        $selectedMonth = (int) ($validated['month'] ?? Carbon::now()->month);

        $periodCarbon = Carbon::createFromDate(
            $selectedYear,
            $selectedMonth,
            1
        )->startOfMonth();

        try {
            $processedCount = $this->sapService->recalculateAll($periodCarbon);

            return redirect()
                ->route('sap.index', [
                    'year' => $selectedYear,
                    'month' => $selectedMonth,
                ])
                ->with(
                    'success',
                    "Perhitungan ulang SAP untuk periode {$periodCarbon->translatedFormat('F Y')} berhasil diperbarui ({$processedCount} karyawan diproses)."
                );

        } catch (Throwable $e) {
            Log::error('Kesalahan saat menjalankan hitung ulang SAP: '.$e->getMessage(), [
                'exception' => $e,
                'year' => $selectedYear,
                'month' => $selectedMonth,
            ]);

            return redirect()
                ->route('sap.index', [
                    'year' => $selectedYear,
                    'month' => $selectedMonth,
                ])
                ->with(
                    'error',
                    'Terjadi kesalahan sistem saat memproses hitung ulang SAP. Hitung ulang dibatalkan dan data rekap tetap seperti semula.'
                );
        }
    }
}