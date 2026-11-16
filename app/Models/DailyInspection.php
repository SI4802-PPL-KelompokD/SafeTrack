<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyInspection extends Model
{
    protected $table = 'daily_inspection';

    protected $guarded = ['id'];

    public function answers()
    {
        return $this->hasMany(InspectionAnswer::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function workArea()
    {
        return $this->belongsTo(WorkArea::class, 'work_area_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
