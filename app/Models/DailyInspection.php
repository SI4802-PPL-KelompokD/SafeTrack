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
}
