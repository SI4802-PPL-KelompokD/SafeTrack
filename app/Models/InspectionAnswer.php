<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionAnswer extends Model
{
    protected $fillable = ['daily_inspection_id', 'inspection_question_id', 'question_snapshot', 'answer', 'notes'];

    public function findings(): HasMany
    {
        return $this->hasMany(InspectionFinding::class);
    }
}
