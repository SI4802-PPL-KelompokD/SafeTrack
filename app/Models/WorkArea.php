<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WorkArea extends Model {
 
 protected $table='work_areas';
 protected $guarded=['*'];
 public function questions(){return $this->hasMany(InspectionQuestion::class);}
}
