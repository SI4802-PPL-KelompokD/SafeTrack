<?php

namespace App\Services;

use App\Models\DailyInspection;
use App\Models\HazardReport;
use App\Models\HazardResolution;
use App\Models\Reward;
use App\Models\SafetyTalk;
use App\Models\SapEntry;
use App\Models\SapMonthlySummary;
use App\Models\SapPointRule;
use App\Models\SapTarget;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SapCalculationService
{
    /**
     * Standard SAP Activities as defined in SafeTrack system.
     */
    public const ACTIVITY_HAZARD = 'hazard_verified';
    public const ACTIVITY_INSPECTION = 'inspection_reviewed';
    public const ACTIVITY_SAFETY_TALK = 'safety_talk_published';
    public const ACTIVITY_RESOLUTION = 'resolution_accepted';

    /**
     * Hitung poin SAP bulanan untuk seorang karyawan (UC-25).
     *
     * @param User $user
     * @param Carbon|string|null $period
     * @return SapMonthlySummary
     */
    public function calculateForUser(User $user, $period = null): SapMonthlySummary
    {
        $periodCarbon = $period ? Carbon::parse($period)->startOfMonth() : Carbon::now()->startOfMonth();
        $periodDate = $periodCarbon->format('Y-m-01');
        $startOfMonth = $periodCarbon->copy()->startOfMonth();
        $endOfMonth = $periodCarbon->copy()->endOfMonth();

        try {
            return DB::transaction(function () use ($user, $periodCarbon, $periodDate, $startOfMonth, $endOfMonth) {
                // Pastikan rule poin aktif tersedia untuk setiap kegiatan
                $rules = $this->getActivePointRules($periodDate);

                // 1. Ambil data Hazard Report valid bulan berjalan
                $hazardReports = HazardReport::where('reporter_id', $user->id)
                    ->whereBetween('reported_at', [$startOfMonth, $endOfMonth])
                    ->whereNull('deleted_at')
                    ->where(function ($query) {
                        $query->whereNotNull('verified_at')
                            ->orWhereIn('status', ['open', 'in_progress', 'closed']);
                    })
                    ->get();

                foreach ($hazardReports as $hazard) {
                    $earnedAt = $hazard->verified_at ?? $hazard->reported_at;
                    $this->awardEntryIfNew(
                        $user->id,
                        self::ACTIVITY_HAZARD,
                        $hazard->id,
                        $rules[self::ACTIVITY_HAZARD],
                        $earnedAt,
                        $periodDate
                    );
                }

                // 2. Ambil data Inspeksi Harian valid bulan berjalan
                $dailyInspections = DailyInspection::where('inspector_id', $user->id)
                    ->whereBetween('inspection_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->whereIn('status', ['submitted', 'reviewed', 'closed'])
                    ->get();

                foreach ($dailyInspections as $inspection) {
                    $earnedAt = $inspection->reviewed_at ?? Carbon::parse($inspection->inspection_date);
                    $this->awardEntryIfNew(
                        $user->id,
                        self::ACTIVITY_INSPECTION,
                        $inspection->id,
                        $rules[self::ACTIVITY_INSPECTION],
                        $earnedAt,
                        $periodDate
                    );
                }

                // 3. Ambil data Safety Talk terverifikasi / dipublikasikan bulan berjalan
                $attendedTalkIds = DB::table('safety_talk_attendees')
                    ->join('safety_talks', 'safety_talk_attendees.safety_talk_id', '=', 'safety_talks.id')
                    ->where('safety_talk_attendees.user_id', $user->id)
                    ->where(function ($q) {
                        $q->where('safety_talks.status', 'published')
                            ->orWhereNotNull('safety_talks.verified_at');
                    })
                    ->whereBetween(DB::raw('COALESCE(safety_talks.held_at, safety_talks.published_at, safety_talks.created_at)'), [$startOfMonth, $endOfMonth])
                    ->pluck('safety_talks.id')
                    ->toArray();

                $readTalkIds = DB::table('safety_talk_reads')
                    ->join('safety_talks', 'safety_talk_reads.safety_talk_id', '=', 'safety_talks.id')
                    ->where('safety_talk_reads.user_id', $user->id)
                    ->where(function ($q) {
                        $q->where('safety_talks.status', 'published')
                            ->orWhereNotNull('safety_talks.verified_at');
                    })
                    ->whereBetween('safety_talk_reads.read_at', [$startOfMonth, $endOfMonth])
                    ->pluck('safety_talks.id')
                    ->toArray();

                $createdTalkIds = SafetyTalk::where('created_by', $user->id)
                    ->where(function ($q) {
                        $q->where('status', 'published')
                            ->orWhereNotNull('verified_at');
                    })
                    ->whereBetween(DB::raw('COALESCE(held_at, published_at, created_at)'), [$startOfMonth, $endOfMonth])
                    ->pluck('id')
                    ->toArray();

                $uniqueTalkIds = array_unique(array_merge($attendedTalkIds, $readTalkIds, $createdTalkIds));

                foreach ($uniqueTalkIds as $talkId) {
                    $this->awardEntryIfNew(
                        $user->id,
                        self::ACTIVITY_SAFETY_TALK,
                        $talkId,
                        $rules[self::ACTIVITY_SAFETY_TALK],
                        $startOfMonth,
                        $periodDate
                    );
                }

                // 4. Ambil data Penyelesaian Laporan Bahaya (Hazard Resolution) yang valid/diterima
                $resolutions = HazardResolution::where('submitted_by', $user->id)
                    ->where('status', 'accepted')
                    ->whereBetween(DB::raw('COALESCE(submitted_at, created_at)'), [$startOfMonth, $endOfMonth])
                    ->get();

                foreach ($resolutions as $res) {
                    $earnedAt = $res->reviewed_at ?? $res->submitted_at ?? $res->created_at;
                    $this->awardEntryIfNew(
                        $user->id,
                        self::ACTIVITY_RESOLUTION,
                        $res->id,
                        $rules[self::ACTIVITY_RESOLUTION],
                        $earnedAt,
                        $periodDate
                    );
                }

                // 5. Hitung total poin valid pada ledger sap_entries
                $totalPoints = (int) SapEntry::where('user_id', $user->id)
                    ->where('period', $periodDate)
                    ->where('is_void', false)
                    ->sum('points');

                $hazardCount = count($hazardReports);
                $inspectionCount = count($dailyInspections);
                $safetyTalkCount = count($uniqueTalkIds);
                $resolutionCount = count($resolutions);

                $departmentId = $user->department_id;
                $isHseSnapshot = (bool) ($user->department?->is_hse ?? false);

                // 6. UC-26: Konversi Poin SAP (Reward untuk Non-HSE / Pemenuhan Target untuk HSE)
                $conversion = $this->convertPoints($user, $totalPoints, $periodDate, $isHseSnapshot);
                $targetMet = $conversion['target_met'];
                $rewardEligible = $conversion['reward_eligible'];
                $eligibleRewardId = $conversion['eligible_reward_id'];

                // 7. Simpan / perbarui rekap SAP bulanan (sap_monthly_summaries)
                return SapMonthlySummary::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'period' => $periodDate,
                    ],
                    [
                        'department_id' => $departmentId,
                        'is_hse_snapshot' => $isHseSnapshot,
                        'hazard_count' => $hazardCount,
                        'inspection_count' => $inspectionCount,
                        'resolution_count' => $resolutionCount,
                        'safety_talk_count' => $safetyTalkCount,
                        'total_points' => $totalPoints,
                        'target_met' => $targetMet,
                        'reward_eligible' => $rewardEligible,
                        'eligible_reward_id' => $eligibleRewardId,
                        'calculated_at' => Carbon::now(),
                    ]
                );
            });
        } catch (\Throwable $e) {
            Log::error("Kegagalan perhitungan poin SAP untuk pengguna ID {$user->id} periode {$periodDate}: " . $e->getMessage(), [
                'user_id' => $user->id,
                'period' => $periodDate,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Hitung ulang rekap SAP bulanan untuk semua karyawan aktif (UC-28).
     *
     * @param Carbon|string|null $period
     * @return int Jumlah karyawan yang berhasil dihitung
     */
    public function recalculateAll($period = null): int
    {
        $periodCarbon = $period ? Carbon::parse($period)->startOfMonth() : Carbon::now()->startOfMonth();

        // Ambil semua pengguna aktif yang memiliki departemen
        $users = User::where('is_active', true)
            ->whereNotNull('department_id')
            ->whereNull('deleted_at')
            ->get();

        $processed = 0;
        foreach ($users as $user) {
            $this->calculateForUser($user, $periodCarbon);
            $processed++;
        }

        return $processed;
    }

    /**
     * Memastikan ledger sap_entries hanya mencatat satu kali per aktivitas & sumber (Idempoten).
     */
    protected function awardEntryIfNew(int $userId, string $activity, int $sourceId, SapPointRule $rule, $earnedAt, string $periodDate): SapEntry
    {
        return SapEntry::firstOrCreate(
            [
                'activity' => $activity,
                'source_id' => $sourceId,
                'user_id' => $userId,
            ],
            [
                'point_rule_id' => $rule->id,
                'points' => $rule->points,
                'earned_at' => $earnedAt,
                'period' => $periodDate,
                'is_void' => false,
            ]
        );
    }

    /**
     * Mengambil atau membuat aturan poin default untuk periode tersebut.
     *
     * @param string $periodDate
     * @return array<string, SapPointRule>
     */
    protected function getActivePointRules(string $periodDate): array
    {
        $activities = [
            self::ACTIVITY_HAZARD,
            self::ACTIVITY_INSPECTION,
            self::ACTIVITY_SAFETY_TALK,
            self::ACTIVITY_RESOLUTION,
        ];

        $rules = [];
        foreach ($activities as $activity) {
            $rule = SapPointRule::where('activity', $activity)
                ->where('effective_from', '<=', $periodDate)
                ->where(function ($q) use ($periodDate) {
                    $q->whereNull('effective_until')
                        ->orWhere('effective_until', '>=', $periodDate);
                })
                ->orderByDesc('effective_from')
                ->first();

            if (!$rule) {
                // Buat rule fallback jika belum ada
                $rule = SapPointRule::firstOrCreate(
                    [
                        'activity' => $activity,
                        'effective_from' => '2020-01-01',
                    ],
                    [
                        'points' => 1,
                        'effective_until' => null,
                    ]
                );
            }

            $rules[$activity] = $rule;
        }

        return $rules;
    }

    /**
     * UC-26: Mengonversi Poin SAP (Reward / Target).
     *
     * Alur:
     * - Untuk Non-HSE: Konversi poin menjadi reward menggunakan tabel rewards (name & required_points).
     * - Untuk HSE: Konversi poin menjadi pemenuhan target bulanan (KPI) berdasarkan aturan konversi SAP (UC-27/PBI-23).
     *   Jika aturan konversi HSE belum ditetapkan, jangan jalankan konversi dan jangan ubah target_met menjadi true.
     *
     * @param User $user
     * @param int $totalPoints
     * @param string $periodDate
     * @param bool $isHse
     * @return array{target_met: ?bool, reward_eligible: bool, eligible_reward_id: ?int}
     */
    public function convertPoints(User $user, int $totalPoints, string $periodDate, bool $isHse): array
    {
        try {
            if ($isHse) {
                // Karyawan Departemen HSE: Konversi poin menjadi pemenuhan target bulanan (KPI)
                // Ambang pemenuhan target HSE diambil dari aturan konversi SAP (UC-27 / PBI-23)
                $hseRule = SapPointRule::whereIn('activity', ['hse_target_threshold', 'hse_target', 'target_threshold'])
                    ->where('effective_from', '<=', $periodDate)
                    ->where(function ($q) use ($periodDate) {
                        $q->whereNull('effective_until')
                            ->orWhere('effective_until', '>=', $periodDate);
                    })
                    ->orderByDesc('effective_from')
                    ->first();

                // Alur Alternatif (2'): Jika aturan konversi belum ditetapkan / tidak tersedia, jangan konversi dan jangan set target_met menjadi true
                if (!$hseRule) {
                    return [
                        'target_met' => null,
                        'reward_eligible' => false,
                        'eligible_reward_id' => null,
                    ];
                }

                $threshold = $hseRule->points;
                // Alur Normal (4) & Alur Alternatif (3'): Tetapkan target_met jika poin mencapai ambang
                $targetMet = $totalPoints >= $threshold;

                return [
                    'target_met' => $targetMet,
                    'reward_eligible' => false,
                    'eligible_reward_id' => null,
                ];
            }

            // Karyawan Non-HSE: Konversi poin menjadi hak reward
            // Aturan ambang reward diambil dari tabel rewards yang aktif (UC-27)
            $eligibleReward = Reward::where('is_active', true)
                ->where('required_points', '<=', $totalPoints)
                ->orderByDesc('required_points')
                ->first();

            // Alur Normal (3): Tandai karyawan berhak atas reward sesuai ambang poin
            // Alur Alternatif (3'): Jika poin belum mencapai ambang atau aturan belum ada, tidak ditandai reward
            if ($eligibleReward) {
                return [
                    'target_met' => null,
                    'reward_eligible' => true,
                    'eligible_reward_id' => $eligibleReward->id,
                ];
            }

            return [
                'target_met' => null,
                'reward_eligible' => false,
                'eligible_reward_id' => null,
            ];
        } catch (\Throwable $e) {
            // Error Situation: Catat kegagalan dan status tidak berubah
            Log::error("Kegagalan konversi poin SAP (UC-26) untuk pengguna ID {$user->id}: " . $e->getMessage(), [
                'user_id' => $user->id,
                'total_points' => $totalPoints,
                'period' => $periodDate,
                'is_hse' => $isHse,
                'exception' => $e,
            ]);

            return [
                'target_met' => null,
                'reward_eligible' => false,
                'eligible_reward_id' => null,
            ];
        }
    }
}

