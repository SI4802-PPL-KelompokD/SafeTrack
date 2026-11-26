<?php

namespace App\Observers;

use App\Jobs\CalculateSapForUserJob;
use App\Models\DailyInspection;

/**
 * UC-25: Trigger perhitungan SAP otomatis saat Inspeksi Harian
 * dibuat atau di-review (include UC-16 Mengisi Inspeksi Harian).
 */
class DailyInspectionObserver
{
    public function created(DailyInspection $inspection): void
    {
        if ($this->isValidForSap($inspection)) {
            CalculateSapForUserJob::dispatch(
                $inspection->inspector_id,
                'auto_inspection',
            );
        }
    }

    public function updated(DailyInspection $inspection): void
    {
        if ($inspection->wasChanged('status') && $this->isValidForSap($inspection)) {
            CalculateSapForUserJob::dispatch(
                $inspection->inspector_id,
                'auto_inspection',
            );
        }
    }

    private function isValidForSap(DailyInspection $inspection): bool
    {
        return in_array($inspection->status, ['submitted', 'reviewed', 'closed'], true);
    }
}
