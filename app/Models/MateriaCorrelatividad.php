<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class MateriaCorrelatividad extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_materia_correlatividad';
  protected $primaryKey = 'mco_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mco_id',
    'mat_id',
    'mat_id_materia',
    'tco_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'mco_id',
      'id_materia' => 'mat_id',
      'correlatividad_id_materia' => 'mat_id_materia',
      'id_tipo_correlatividad' => 'tco_id',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'id_materia',
      'correlatividad_id_materia',
      'id_tipo_correlatividad',
      'id_usuario',
  ];

  public function materia(){
    return $this->hasOne('App\Models\Materia','mat_id','mat_id');
  }

  public function correlatividad(){
    return $this->hasOne('App\Models\Materia','mat_id','mat_id_materia');
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoCorrelatividad','tco_id','tco_id');
  }

}
