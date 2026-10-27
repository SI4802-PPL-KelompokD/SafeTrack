<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapPointRule extends Model
{
    protected $table = 'sap_point_rules';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function entries()
    {
        return $this->hasMany(SapEntry::class, 'point_rule_id');
    }
}
