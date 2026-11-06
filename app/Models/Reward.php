<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $table = 'rewards';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'required_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function monthlySummaries()
    {
        return $this->hasMany(SapMonthlySummary::class, 'eligible_reward_id');
    }

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class, 'reward_id');
    }
}
