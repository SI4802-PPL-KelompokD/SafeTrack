<?php

namespace App\Http\Controllers;

use App\Models\SapMonthlySummary;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SapController extends Controller
{
    protected SapCalculationService $sapService;

    public function __construct(SapCalculationService $sapService)
    {
        $this->sapService = $sapService;
    }

    /**
     * Tampilkan halaman Pencapaian SAP Bulanan (UC-28 Langkah 1).
     */
    public function index(Request $request)
    {
        $selectedYear = (int) $request->input('year', Carbon::now()->year);
        $selectedMonth = (int) $request->input('month', Carbon::now()->month);

        $periodCarbon = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $periodDate = $periodCarbon->format('Y-m-01');

        // Query rekap SAP bulanan dengan relasi karyawan, departemen, dan reward
        $summariesQuery = SapMonthlySummary::with(['user.employeeRegistry', 'department', 'eligibleReward'])
            ->where('period', $periodDate);

        // Filter pencarian nama / NIK jika ada
        if ($search = $request->input('search')) {
            $summariesQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhereHas('employeeRegistry', function ($reg) use ($search) {
                      $reg->where('nik', 'like', "%{$search}%")
                          ->orWhere('full_name', 'like', "%{$search}%");
                  });
            });
        }

        $summaries = $summariesQuery->orderByDesc('total_points')->paginate(15)->withQueryString();

        // Statistik ringkasan untuk periode ini
        $baseStatsQuery = SapMonthlySummary::where('period', $periodDate);
        $totalPoints = (int) $baseStatsQuery->sum('total_points');
        $participatingEmployees = (int) (clone $baseStatsQuery)->where('total_points', '>', 0)->count();
        $totalEmployeesRecapped = (int) (clone $baseStatsQuery)->count();
        $targetMetCount = (int) (clone $baseStatsQuery)->where('target_met', true)->count();
        $rewardEligibleCount = (int) (clone $baseStatsQuery)->where('reward_eligible', true)->count();
        $averagePoints = $totalEmployeesRecapped > 0 ? round($totalPoints / $totalEmployeesRecapped, 1) : 0;

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
        ]);
    }

    /**
     * Eksekusi Hitung Ulang SAP Bulanan oleh Admin (UC-28 Langkah 2-4).
     */
    public function recalculate(Request $request)
    {
        $selectedYear = (int) $request->input('year', Carbon::now()->year);
        $selectedMonth = (int) $request->input('month', Carbon::now()->month);

        $periodCarbon = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();

        try {
            // Jalankan perhitungan ulang otomatis untuk seluruh karyawan aktif
            $processedCount = $this->sapService->recalculateAll($periodCarbon);

            return redirect()->route('sap.index', [
                'year' => $selectedYear,
                'month' => $selectedMonth,
            ])->with('success', "Perhitungan ulang SAP untuk periode {$periodCarbon->translatedFormat('F Y')} berhasil diperbarui ({$processedCount} karyawan diproses).");
        } catch (\Throwable $e) {
            // Alternative Process 3': Jika terjadi kesalahan pemrosesan, rekap SAP tetap seperti sebelum hitung ulang
            Log::error('Kesalahan saat menjalankan hitung ulang SAP: ' . $e->getMessage(), [
                'exception' => $e,
                'year' => $selectedYear,
                'month' => $selectedMonth,
            ]);

            return redirect()->route('sap.index', [
                'year' => $selectedYear,
                'month' => $selectedMonth,
            ])->with('error', 'Terjadi kesalahan sistem saat memproses hitung ulang SAP. Hitung ulang dibatalkan dan data rekap tetap seperti semula.');
        }
    }
}
