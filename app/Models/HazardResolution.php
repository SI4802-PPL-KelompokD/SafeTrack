<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HazardResolution extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Menunggu verifikasi HSE',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    public const STATUS_BADGES = [
        'pending' => 'amber',
        'accepted' => 'green',
        'rejected' => 'red',
    ];

    protected $table = 'penyelesaian';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(HazardReport::class, 'hazard_report_id')->withTrashed();
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ResolutionPhoto::class, 'hazard_resolution_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? (string) $this->status;
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'gray';
    }
}
