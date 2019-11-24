<?php

namespace App\Models;

use App\Models\AsistenciaAlumno;
use App\Models\Comision\ExamenAlumno;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ComisionAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comision_alumno';
  protected $primaryKey = 'cal_id';

  protected $with = [
    'tipo',
    'alumno',
    'comision',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'cal_id',
    'alu_id',
    'com_id',
    'ins_id',
    'usu_id',
    'usu_id_baja',
    'deleted_at',
    'com_nota',
    'tca_id',
    'com_observaciones',
  ];

  protected $maps = [
      'id' => 'cal_id',
      'id_alumno' => 'alu_id',
      'id_comision' => 'com_id',
      'id_inscripcion' => 'ins_id',
      'id_usuario' => 'usu_id',
      'nota' => 'com_nota',
      'id_tipo_condicion_alumno' => 'tca_id',
      'observaciones' => 'com_observaciones',
  ];

  protected $appends = [
      'id',
      'id_alumno',
      'id_comision',
      'id_inscripcion',
      'id_usuario',
      'nota',
      'id_tipo_condicion_alumno',
      'observaciones',

      'asistencia_presente_promedio',
      'examen_presente_promedio',
      'examen_parcial_promedio',
      'examen_practico_promedio',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function inscripcion(){
    return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
  }

  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\Mesa\TipoCondicionAlumno','tca_id','tca_id');
  }

  public function getAsistenciaPresentePromedioAttribute(){
    $id_comision = $this['com_id'];
    return (AsistenciaAlumno::selectRaw('estado, AVG(IF(taa_id=4,1,0)) as avg')
          ->where('id_alumno',$this['alu_id'])
          ->whereHas('asistencia',function($q)use($id_comision){
            $q->where('id_comision',$id_comision);
          })
          ->where('estado',1)
          ->groupBy('estado')
          ->first()->avg??0)*100;
  }

  public function getExamenPresentePromedioAttribute(){
    $id_comision = $this['com_id'];
    return (ExamenAlumno::selectRaw('estado, AVG(IF(taa_id=4,1,0)) as avg')
          ->where('id_alumno',$this['alu_id'])
          ->whereHas('examen',function($q)use($id_comision){
            $q->where('id_comision',$id_comision);
          })
          ->where('estado',1)
          ->groupBy('estado')
          ->first()->avg??0)*100;
  }

  public function getExamenParcialPromedioAttribute(){
    $id_comision = $this['com_id'];
    return ExamenAlumno::selectRaw('estado, AVG(cae_nota) as avg')
          ->where('id_alumno',$this['alu_id'])
          ->whereHas('examen',function($q)use($id_comision){
            $q->where('id_comision',$id_comision);
          })
          ->where('estado',1)
          ->whereIn('id_tipo_asistencia_alumno',[1,2])
          ->groupBy('estado')
          ->first()->avg??0;
  }
  public function getExamenPracticoPromedioAttribute(){
    $id_comision = $this['com_id'];
    return ExamenAlumno::selectRaw('estado, AVG(cae_nota) as avg')
          ->where('id_alumno',$this['alu_id'])
          ->whereHas('examen',function($q)use($id_comision){
            $q->where('id_comision',$id_comision);
          })
          ->where('estado',1)
          ->whereIn('id_tipo_asistencia_alumno',[3])
          ->groupBy('estado')
          ->first()->avg??0;
  }
}
