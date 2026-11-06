<?php
namespace App\Models;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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

 public function getProfilePhotoUrlAttribute(): ?string
 {
  if ($this->profile_photo_path) {
   return Storage::disk('public')->url($this->profile_photo_path);
  }
  return null;
 }

 public function getInitialsAttribute(): string
 {
  $parts = preg_split('/\s+/', trim($this->name ?? ''));
  $initials = '';
  foreach ($parts as $part) {
   if (!empty($part)) {
    $initials .= mb_substr($part, 0, 1);
   }
   if (mb_strlen($initials) >= 2) {
    break;
   }
  }
  return strtoupper($initials ?: 'ST');
 }

 public function getNikAttribute(): ?string
 {
  return $this->employeeRegistry?->nik ?? $this->username;
 }

 public function getRoleLabelAttribute(): string
 {
  if ($this->role === 'super_admin') {
   return 'Super Admin';
  }
  if ($this->role === 'admin') {
   return 'Admin';
  }
  if ($this->department?->is_hse) {
   return 'Karyawan HSE';
  }
  return 'Karyawan';
 }
}
