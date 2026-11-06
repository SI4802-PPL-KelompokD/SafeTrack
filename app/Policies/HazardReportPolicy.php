<?php

namespace App\Policies;

use App\Models\HazardReport;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class HazardReportPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_active;
    }

    /**
     * Admin: semua (termasuk arsip).
     * Karyawan: laporan miliknya, atau laporan orang lain yang sudah terverifikasi (open/in_progress/closed).
     * Selain itu 404 agar keberadaan laporan tidak bocor.
     */
    public function view(User $user, HazardReport $report): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        if ($report->trashed()) {
            return Response::denyAsNotFound();
        }

        $allowed = $report->reporter_id === $user->id
            || in_array($report->status, HazardReport::PUBLIC_STATUSES, true);

        return $allowed ? Response::allow() : Response::denyAsNotFound();
    }
}
