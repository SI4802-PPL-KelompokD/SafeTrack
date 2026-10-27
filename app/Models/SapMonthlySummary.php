<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapMonthlySummary extends Model
{
    protected $table = 'history_sap';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'is_hse_snapshot' => 'boolean',
            'hazard_count' => 'integer',
            'inspection_count' => 'integer',
            'resolution_count' => 'integer',
            'safety_talk_count' => 'integer',
            'total_points' => 'integer',
            'target_met' => 'boolean',
            'reward_eligible' => 'boolean',
            'calculated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function eligibleReward()
    {
        return $this->belongsTo(Reward::class, 'eligible_reward_id');
    }
}
