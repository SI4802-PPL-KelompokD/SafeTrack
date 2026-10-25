<?php
namespace App\Models;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
#[Fillable(['name','username','email','password','profile_photo_path'])]
#[Hidden(['password','remember_token'])]
class User extends Authenticatable {
 /** @use HasFactory<UserFactory> */
 use HasFactory,Notifiable,SoftDeletes;
 protected function casts(): array {
  return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
 }
 public function department(){return $this->belongsTo(Department::class);}
 public function employeeRegistry(){return $this->belongsTo(EmployeeRegistry::class);}
 public function reports(){return $this->hasMany(HazardReport::class,'reporter_id');}
 public function sapMonthlySummaries(){return $this->hasMany(SapMonthlySummary::class);}
 public function sapEntries(){return $this->hasMany(SapEntry::class);}
 public function sapTargets(){return $this->hasMany(SapTarget::class);}
}

