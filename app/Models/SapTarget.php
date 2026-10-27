<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapTarget extends Model
{
    protected $table = 'target_safety_talks';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'target_count' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
