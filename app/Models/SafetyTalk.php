<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SafetyTalk extends Model {
 
 protected $table='safety_talks';
 protected $guarded=['id'];
 public function readers(){return $this->belongsToMany(User::class,'safety_talk_reads')->withPivot('read_at');}
}
