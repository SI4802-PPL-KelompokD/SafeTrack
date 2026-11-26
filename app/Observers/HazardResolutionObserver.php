<?php

namespace App\Observers;

use App\Jobs\CalculateSapForUserJob;
use App\Models\HazardResolution;

/**
 * UC-25: Trigger perhitungan SAP otomatis saat Penyelesaian Laporan Bahaya
 * diterima/diverifikasi (include UC-12 Memverifikasi Penyelesaian Laporan).
 */
class HazardResolutionObserver
{
    public function created(HazardResolution $resolution): void
    {
        if ($resolution->status === 'accepted') {
            CalculateSapForUserJob::dispatch(
                $resolution->submitted_by,
                'auto_resolution',
            );
        }
    }

    public function updated(HazardResolution $resolution): void
    {
        if ($resolution->wasChanged('status') && $resolution->status === 'accepted') {
            CalculateSapForUserJob::dispatch(
                $resolution->submitted_by,
                'auto_resolution',
            );
        }
    }
}
