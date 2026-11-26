<?php

namespace App\Jobs;

use App\Models\SapCalculationLog;
use App\Models\User;
use App\Services\SapCalculationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CalculateSapForUserJob implements ShouldQueue
{
    use Queueable;

    /**
     * UC-25: Job latar belakang untuk menghitung poin SAP karyawan
     * setelah data sumber (hazard, inspeksi, safety talk, resolusi) berubah.
     *
     * Alur Alternatif A1: Jika perhitungan gagal, kegagalan dicatat
     * pada log perhitungan dan aksi sumber tidak ikut dibatalkan.
     */
    public function __construct(
        public int $userId,
        public string $triggerType,
        public ?string $period = null,
    ) {}

    public function handle(SapCalculationService $sapService): void
    {
        $user = User::find($this->userId);

        if (! $user || ! $user->is_active || ! $user->department_id) {
            return;
        }

        $periodCarbon = $this->period
            ? Carbon::parse($this->period)->startOfMonth()
            : Carbon::now()->startOfMonth();

        try {
            $sapService->calculateForUser($user, $periodCarbon);

            SapCalculationLog::create([
                'trigger_type' => $this->triggerType,
                'user_id' => $user->id,
                'period' => $periodCarbon->format('Y-m-01'),
                'status' => 'success',
                'processed_count' => 1,
            ]);
        } catch (\Throwable $e) {
            // Alur Alternatif A1: Catat kegagalan, aksi sumber tetap berhasil
            Log::error("UC-25: Gagal menghitung SAP otomatis untuk user #{$user->id}: ".$e->getMessage());

            SapCalculationLog::create([
                'trigger_type' => $this->triggerType,
                'user_id' => $user->id,
                'period' => $periodCarbon->format('Y-m-01'),
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
            ]);
        }
    }
}
