<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

class EmployeeRegistry extends Model
{
    protected $table = 'nik';

    protected $guarded = ['*'];

    public function getTable()
    {
        return Schema::hasTable('nik') ? 'nik' : 'employee_registry';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
