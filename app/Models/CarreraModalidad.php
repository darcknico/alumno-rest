<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class CarreraModalidad extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_carrera_modalidad';
  protected $primaryKey = 'cmo_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'cmo_id',
    'car_id',
    'mod_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'cmo_id',
      'id_modalidad' => 'mod_id',
      'id_carrera' => 'car_id',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'id_modalidad',
      'id_carrera',
      'id_usuario',
  ];


  public function modalidad(){
    return $this->hasOne('App\Models\Modalidad','mod_id','mod_id');
  }

  public function carrera(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id');
  }

}
