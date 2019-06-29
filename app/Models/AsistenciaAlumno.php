<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AsistenciaAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_asistencia_alumno';
  protected $primaryKey = 'aal_id';

  protected $with = [
    'tipo',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'aal_id',
    'asi_id',
    'alu_id',
    'taa_id',
    'aal_observaciones',
  ];

  protected $maps = [
      'id' => 'aal_id',
      'id_asistencia' => 'asi_id',
      'id_alumno' => 'alu_id',
      'id_tipo_asistencia_alumno' => 'taa_id',
      'observaciones' => 'aal_observaciones',
  ];

  protected $appends = [
      'id',
      'id_asistencia',
      'id_alumno',
      'id_tipo_asistencia_alumno',
      'observaciones',
  ];

  public function tipo(){
    return $this->hasOne('App\Models\TipoAsistenciaAlumno','taa_id','taa_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function asistencia(){
    return $this->hasOne('App\Models\Asistencia','asi_id','asi_id');
  }

}
