<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menulis ke tabel `audit_logs` (pola sama dengan HazardClaimService).
 * Panggil DI DALAM transaksi agar audit gagal = perubahan ikut rollback.
 */
class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function log(?User $actor, string $action, Model $entity, ?array $before = null, ?array $after = null): void
    {
        DB::table('audit_logs')->insert([
            'actor_id' => $actor?->id,
            'action' => Str::limit($action, 60, ''),
            'entity_type' => Str::snake(class_basename($entity)),
            'entity_id' => $entity->getKey(),
            'before_values' => $before === null ? null : json_encode($before, JSON_UNESCAPED_UNICODE),
            'after_values' => $after === null ? null : json_encode($after, JSON_UNESCAPED_UNICODE),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
