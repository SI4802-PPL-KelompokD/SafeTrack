<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolutionPhoto extends Model
{
    protected $table = 'resolution_photos';

    protected $guarded = ['id'];

    public function resolution(): BelongsTo
    {
        return $this->belongsTo(HazardResolution::class, 'hazard_resolution_id');
    }
}
