<?php

namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ExamenAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comision_examen_alumno';
  protected $primaryKey = 'cea_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $with = [
    'tipo',
  ];

  protected $hidden = [
    'cea_id',
    'cex_id',
    'alu_id',
    'taa_id',
    'cae_nota',
    'cae_observaciones',
  ];

  protected $maps = [
      'id' => 'cea_id',
      'id_comision_examen' => 'cex_id',
      'id_alumno' => 'alu_id',
      'id_tipo_asistencia_alumno' => 'taa_id',
      'nota'=>'cae_nota',
      'observaciones' => 'cae_observaciones',
  ];

  protected $appends = [
      'id',
      'id_comision_examen',
      'id_alumno',
      'id_tipo_asistencia_alumno',
      'nota',
      'observaciones',

  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoAsistenciaAlumno','taa_id','taa_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function examen(){
    return $this->hasOne('App\Models\Comision\Examen','cex_id','cex_id');
  }

}
