<?php

namespace App\Http\Controllers;

use App\Models\HazardPhoto;
use App\Models\HazardReport;
use App\Models\ResolutionPhoto;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Foto bukti disimpan PRIVAT (docs/safetrack/DESIGN.md): tidak ada storage:link.
 * Akses file mengikuti izin melihat laporan induknya.
 */
class HazardPhotoController extends Controller
{
    public function hazard(HazardReport $hazardReport, HazardPhoto $photo): Response
    {
        Gate::authorize('view', $hazardReport);
        abort_unless($photo->hazard_report_id === $hazardReport->id, 404);

        return $this->stream($photo->disk, $photo->path, $photo->mime_type, $photo->original_name);
    }

    public function resolution(HazardReport $hazardReport, ResolutionPhoto $photo): Response
    {
        Gate::authorize('view', $hazardReport);

        $photo->loadMissing('resolution');
        abort_unless($photo->resolution && $photo->resolution->hazard_report_id === $hazardReport->id, 404);

        return $this->stream($photo->disk, $photo->path, $photo->mime_type, $photo->original_name);
    }

    private function stream(string $disk, string $path, string $mime, string $name): Response
    {
        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);

        return $storage->response($path, $name, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
