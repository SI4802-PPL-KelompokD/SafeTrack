<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class EmploymentStatus extends Model
{
    protected $guarded = ['*'];

    public function getTable()
    {
        return $this->table ?? (Schema::hasTable('status_karyawan') ? 'status_karyawan' : 'employment_statuses');
    }

    /**
     * Relasi ke entri employee_registry.
     */
    public function employeeRegistries(): HasMany
    {
        return $this->hasMany(EmployeeRegistry::class, 'employment_status_id');
    }
}
