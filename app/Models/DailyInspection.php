<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyInspection extends Model
{
    protected $fillable = ['client_request_id', 'inspector_id', 'department_id', 'work_area_id', 'shift_id', 'inspection_date', 'status', 'notes'];

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class);
    }
}
