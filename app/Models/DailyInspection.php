<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DailyInspection extends Model {
 
 protected $table='daily_inspections';
 protected $guarded=['*'];
 public function answers(){return $this->hasMany(InspectionAnswer::class);}
}
