<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapCalculationLog extends Model
{
    protected $table = 'sap_log_perhitungan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'processed_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
