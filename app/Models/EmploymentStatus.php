<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentStatus extends Model
{
    protected $guarded = ['*'];

    public function employeeRegistries(): HasMany
    {
        return $this->hasMany(EmployeeRegistry::class);
    }
}
