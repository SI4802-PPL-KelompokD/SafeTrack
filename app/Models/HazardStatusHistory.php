<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardStatusHistory extends Model
{
    /** Nama tabel hasil rename ERD (hazard_status_histories -> hazard_history_status). */
    protected $table = 'hazard_history_status';

    protected $guarded = ['id'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(HazardReport::class, 'hazard_report_id')->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function fromLabel(): string
    {
        return $this->from_status
            ? (HazardReport::STATUS_LABELS[$this->from_status] ?? $this->from_status)
            : '—';
    }

    public function toLabel(): string
    {
        return HazardReport::STATUS_LABELS[$this->to_status] ?? (string) $this->to_status;
    }
}
