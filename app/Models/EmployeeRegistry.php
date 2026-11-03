<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;

class EmployeeRegistry extends Model
{
    protected $guarded = ['*'];

    public function getTable()
    {
        return $this->table ?? (Schema::hasTable('nik') ? 'nik' : 'employee_registry');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_registry_id');
    }
}
