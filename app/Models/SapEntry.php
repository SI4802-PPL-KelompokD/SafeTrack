<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapEntry extends Model
{
    protected $table = 'sap_entries';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'earned_at' => 'datetime',
            'period' => 'date',
            'is_void' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rule()
    {
        return $this->belongsTo(SapPointRule::class, 'point_rule_id');
    }
}
