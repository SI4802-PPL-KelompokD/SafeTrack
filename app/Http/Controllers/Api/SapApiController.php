<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SapMonthlySummary;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SapApiController extends Controller
{
    protected SapCalculationService $sapService;

    public function __construct(SapCalculationService $sapService)
    {
        $this->sapService = $sapService;
    }

    /**
     * Mobile API Ringkasan SAP Personal Karyawan (GET /api/sap/summary)
     */
    public function summary(Request $request)
    {
        $user = $request->user()->load('department');

        $selectedYear = (int) $request->input('year', Carbon::now()->year);
        $selectedMonth = (int) $request->input('month', Carbon::now()->month);

        $periodCarbon = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
        $periodDate = $periodCarbon->format('Y-m-01');

        // Ambil summary atau hitung otomatis jika belum ada
        $summary = SapMonthlySummary::with('eligibleReward')
            ->where('user_id', $user->id)
            ->where('period', $periodDate)
            ->first();

        if (!$summary) {
            $summary = $this->sapService->calculateForUser($user, $periodCarbon);
            $summary->load('eligibleReward');
        }

        $isHse = (bool) ($user->department?->is_hse ?? $summary->is_hse_snapshot);

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $periodDate,
                'period_formatted' => $periodCarbon->translatedFormat('F Y'),
                'is_hse' => $isHse,
                'stats' => [
                    'hazard_count' => (int) $summary->hazard_count,
                    'inspection_count' => (int) $summary->inspection_count,
                    'safety_talk_count' => (int) $summary->safety_talk_count,
                    'resolution_count' => (int) $summary->resolution_count,
                    'total_points' => (int) $summary->total_points,
                ],
                'conversion' => [
                    'type' => $isHse ? 'hse_target' : 'reward',
                    'target_met' => $isHse ? $summary->target_met : null,
                    'target_met_label' => $isHse ? ($summary->target_met === true ? 'Target Terpenuhi' : ($summary->target_met === false ? 'Target Belum Terpenuhi' : 'Aturan Belum Ditetapkan')) : null,
                    'reward_eligible' => (bool) $summary->reward_eligible,
                    'reward' => $summary->eligibleReward ? [
                        'id' => $summary->eligibleReward->id,
                        'name' => $summary->eligibleReward->name,
                        'required_points' => (int) $summary->eligibleReward->required_points,
                        'description' => $summary->eligibleReward->description,
                    ] : null,
                ],
                'calculated_at' => $summary->calculated_at ? $summary->calculated_at->toIso8601String() : null,
            ],
        ], 200);
    }
}
