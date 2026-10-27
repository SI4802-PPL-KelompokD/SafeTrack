<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HazardReport extends Model {
 use \Illuminate\Database\Eloquent\SoftDeletes;
 protected $table='hazard_reports';
 protected $guarded=['id'];
 public function reporter(){return $this->belongsTo(User::class,'reporter_id');}
 public function resolutions(){return $this->hasMany(HazardResolution::class);}
}
