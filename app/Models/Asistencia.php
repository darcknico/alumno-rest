<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Asistencia extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_asistencias';
  protected $primaryKey = 'asi_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'asi_id',
    'asi_fecha',
    'com_id',
    'asi_check_in',
    'asi_check_out',
    'asi_alumnos_cantidad',
    'asi_alumnos_cantidad_presente',
    'asi_responsable_nombre',
    'asi_responsable_apellido',
    'usu_id',
    'usu_id_baja',
    'deleted_at',
    'usu_id_check_in',
    'usu_id_check_out',
  ];

  protected $maps = [
      'id' => 'asi_id',
      'fecha' => 'asi_fecha',
      'id_comision' => 'com_id',
      'check_in' => 'asi_check_in',
      'check_out' => 'asi_check_out',
      'alumnos_cantidad' => 'asi_alumnos_cantidad',
      'alumnos_cantidad_presente' => 'asi_alumnos_cantidad_presente',
      'responsable_nombre' => 'asi_responsable_nombre',
      'responsable_apellido' => 'asi_responsable_apellido',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'fecha',
      'id_comision',
      'check_in',
      'check_out',
      'alumnos_cantidad',
      'alumnos_cantidad_presente',
      'responsable_nombre',
      'responsable_apellido',
      'id_usuario',
  ];

  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function usuario_check_in(){
    return $this->hasOne('App\User','usu_id','usu_id_check_in');
  }

  public function usuario_check_out(){
    return $this->hasOne('App\User','usu_id','usu_id_check_out');
  }

  public function alumnos(){
    return $this->hasMany('App\Models\AsistenciaAlumno','asi_id','asi_id');
  }

}