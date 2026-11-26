<?php

namespace App\Observers;

use App\Jobs\CalculateSapForUserJob;
use App\Models\HazardReport;

/**
 * UC-25: Trigger perhitungan SAP otomatis saat Hazard Report
 * dibuat atau diverifikasi (include UC-06 Membuat Laporan Bahaya).
 */
class HazardReportObserver
{
    public function created(HazardReport $hazardReport): void
    {
        if ($this->isValidForSap($hazardReport)) {
            CalculateSapForUserJob::dispatch(
                $hazardReport->reporter_id,
                'auto_hazard',
            );
        }
    }

    public function updated(HazardReport $hazardReport): void
    {
        // Trigger jika verified_at baru saja di-set atau status berubah menjadi valid
        $statusChanged = $hazardReport->wasChanged('status') && $this->isValidForSap($hazardReport);
        $justVerified = $hazardReport->wasChanged('verified_at') && $hazardReport->verified_at !== null;

        if ($statusChanged || $justVerified) {
            CalculateSapForUserJob::dispatch(
                $hazardReport->reporter_id,
                'auto_hazard',
            );
        }
    }

    private function isValidForSap(HazardReport $hazardReport): bool
    {
        return $hazardReport->verified_at !== null
            || in_array($hazardReport->status, ['open', 'in_progress', 'closed'], true);
    }
}
