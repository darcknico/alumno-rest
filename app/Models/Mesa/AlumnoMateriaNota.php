<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AlumnoMateriaNota extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_alumno_materia_nota';
  protected $primaryKey = 'amn_id';

  protected $casts = [
      'estado'=>'boolean',
      'asistencia'=>'boolean',
  ];

  protected $hidden = [
    'amn_id',
    'alu_id',
    'ins_id',
    'mat_id',
    'amn_asistencia',
    'amn_nota',
    'amn_nota_nombre',
    'amn_observaciones',
    'tca_id',
    'amn_fecha',
    'amn_libro',
    'amn_folio',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'amn_id',
      'id_alumno' => 'alu_id',
      'id_inscripcion' => 'ins_id',
      'id_materia' => 'mat_id',
      'asistencia' => 'amn_asistencia',
      'nota' => 'amn_nota',
      'nota_nombre' => 'amn_nota_nombre',
      'observaciones' => 'amn_observaciones',
      'id_tipo_condicion_alumno' => 'tca_id',
      'fecha' => 'amn_fecha',
      'libro' => 'amn_libro',
      'folio' => 'amn_folio',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'id_alumno',
      'id_inscripcion',
      'id_materia',
      'asistencia',
      'nota',
      'nota_nombre',
      'observaciones',
      'id_tipo_condicion_alumno',
      'fecha',
      'libro',
      'folio',
      'id_usuario',
  ];

  public function condicion(){
    return $this->hasOne('App\Models\Mesa\TipoCondicionAlumno','tca_id','tca_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function inscripcion(){
    return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
  }

  public function materia(){
    return $this->hasOne('App\Models\Materia','mat_id','mat_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

}
