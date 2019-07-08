<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class MesaExamenMateriaAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_mesa_alumno_materia';
  protected $primaryKey = 'mam_id';

  protected $casts = [
      'estado'=>'boolean',
      'asistencia'=>'boolean',
  ];

  protected $hidden = [
    'mam_id',
    'mma_id',
    'alu_id',
    'ins_id',
    'usu_id',
    'mam_asistencia',
    'mam_nota',
    'mam_nota_nombre',
    'tca_id',
    'deleted_at',
    'usu_id_baja',
    'mam_observaciones',
  ];

  protected $maps = [
      'id' => 'mam_id',
      'id_mesa_examen_materia' => 'mma_id',
      'id_alumno' => 'alu_id',
      'id_inscripcion' => 'ins_id',
      'id_usuario' => 'usu_id',
      'asistencia' => 'mam_asistencia',
      'nota' => 'mam_nota',
      'nota_nombre' => 'mam_nota_nombre',
      'observaciones' => 'mam_observaciones',
      'id_tipo_condicion_alumno' => 'tca_id',
  ];

  protected $appends = [
      'id',
      'id_mesa_examen_materia',
      'id_alumno',
      'id_inscripcion',
      'id_usuario',
      'asistencia',
      'nota',
      'nota_nombre',
      'observaciones',
      'id_tipo_condicion_alumno',
  ];


  public function mesa_examen_materia(){
    return $this->hasOne('App\Models\Mesa\MesaExamenMateria','mma_id','mma_id');
  }

  public function condicion(){
    return $this->hasOne('App\Models\Mesa\TipoCondicionAlumno','tca_id','tca_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function inscripcion(){
    return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }


}