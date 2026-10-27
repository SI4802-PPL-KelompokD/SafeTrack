<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SapEntry extends Model
{
    protected $table = 'pencapaian';

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SapPointRule::class, 'point_rule_id');
    }
}
