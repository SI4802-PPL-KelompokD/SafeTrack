<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionAnswer extends Model
{
    protected $table = 'inspection_answer';

    protected $guarded = ['*'];

    public function findings()
    {
        return $this->hasMany(InspectionFinding::class);
    }
}
