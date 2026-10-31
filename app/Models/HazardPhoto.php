<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HazardPhoto extends Model
{
    protected $table = 'hazard_photos';

    protected $guarded = ['id'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(HazardReport::class, 'hazard_report_id')->withTrashed();
    }
}
