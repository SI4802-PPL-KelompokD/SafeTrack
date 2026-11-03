<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name','username','email','password','profile_photo_path'])]
#[Hidden(['password','remember_token'])]
class User extends Authenticatable {
 /** @use HasFactory<UserFactory> */
 use HasApiTokens,HasFactory,Notifiable,SoftDeletes;
 protected function casts(): array {
  return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
 }
 /** Admin/HSE = role super_admin atau admin (sama dengan middleware EnsureAdmin). */
 public function isAdmin(): bool {return in_array($this->role,['super_admin','admin'],true);}
 public function department(){return $this->belongsTo(Department::class);}
 public function employeeRegistry(){return $this->belongsTo(EmployeeRegistry::class);}
 public function reports(){return $this->hasMany(HazardReport::class,'reporter_id');}
 public function sapMonthlySummaries(){return $this->hasMany(SapMonthlySummary::class);}
 public function sapEntries(){return $this->hasMany(SapEntry::class);}
 public function sapTargets(){return $this->hasMany(SapTarget::class);}

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'profile_photo_path',
    'department_id',
    'employee_registry_id',
    'role',
    'is_active',
    'deleted_by',
    'deletion_reason',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeRegistry(): BelongsTo
    {
        return $this->belongsTo(EmployeeRegistry::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(HazardReport::class, 'reporter_id');
    }

    public function deletedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function employmentStatus(): HasOneThrough
    {
        return $this->hasOneThrough(
            EmploymentStatus::class,
            EmployeeRegistry::class,
            'id',
            'id',
            'employee_registry_id',
            'employment_status_id'
        );
    }

    /**
     * Scope pencarian berdasarkan nama, NIK, email, atau username (FR-06).
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $subQuery) use ($term) {
            $subQuery->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhereHas('employeeRegistry', function (Builder $er) use ($term) {
                    $er->where('nik', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope filter berdasarkan departemen.
     */
    public function scopeFilterDepartemen(Builder $query, mixed $departmentId): Builder
    {
        if (blank($departmentId)) {
            return $query;
        }

        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope filter berdasarkan status aktif akun.
     */
    public function scopeFilterStatus(Builder $query, mixed $status): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        $isActive = filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isActive === null) {
            $isActive = in_array((string) $status, ['1', 'aktif', 'active'], true);
        }

        return $query->where('is_active', $isActive);
    }
}
