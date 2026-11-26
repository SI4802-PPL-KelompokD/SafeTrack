<?php

namespace App\Observers;

use App\Jobs\CalculateSapForUserJob;
use App\Models\SafetyTalk;
use Illuminate\Support\Facades\DB;

/**
 * UC-25: Trigger perhitungan SAP otomatis saat Safety Talk
 * diverifikasi atau dipublikasikan.
 *
 * Menghitung SAP untuk: pembuat (created_by) dan semua peserta (attendees).
 */
class SafetyTalkObserver
{
    public function updated(SafetyTalk $safetyTalk): void
    {
        $justPublished = $safetyTalk->wasChanged('status') && $safetyTalk->status === 'published';
        $justVerified = $safetyTalk->wasChanged('verified_at') && $safetyTalk->verified_at !== null;

        if (! $justPublished && ! $justVerified) {
            return;
        }

        // Hitung SAP untuk pembuat
        CalculateSapForUserJob::dispatch(
            $safetyTalk->created_by,
            'auto_safety_talk',
        );

        // Hitung SAP untuk semua peserta (attendees)
        $attendeeIds = DB::table('safety_talk_attendees')
            ->where('safety_talk_id', $safetyTalk->id)
            ->where('user_id', '!=', $safetyTalk->created_by)
            ->pluck('user_id');

        foreach ($attendeeIds as $attendeeId) {
            CalculateSapForUserJob::dispatch(
                $attendeeId,
                'auto_safety_talk',
            );
        }
    }
}
