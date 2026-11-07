<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionAnswer extends Model
{
    protected $table = 'inspection_answer';

    protected $fillable = ['inspection_question_id', 'question_snapshot', 'answer', 'notes'];

    public function findings()
    {
        return $this->hasMany(InspectionFinding::class);
    }
}
