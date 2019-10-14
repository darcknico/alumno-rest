<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Comision extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comisiones';
  protected $primaryKey = 'com_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'com_id',
    'usu_id',
    'car_id',
    'mat_id',
    'sed_id',
    'com_anio',
    'com_numero',
    'com_alumnos_cantidad',
    'com_responsable_nombre',
    'com_responsable_apellido',
    'com_cerrado',
    'mod_id',
    'usu_id_alta',
    'usu_id_baja',
    'deleted_at',
  ];

  protected $maps = [
      'id' => 'com_id',
      'id_usuario' => 'usu_id',
      'id_carrera' => 'car_id',
      'id_materia' => 'mat_id',
      'id_sede' => 'sed_id',
      'anio' => 'com_anio',
      'numero' => 'com_numero',
      'alumnos_cantidad' => 'com_alumnos_cantidad',
      'responsable_nombre' => 'com_responsable_nombre',
      'responsable_apellido' => 'com_responsable_apellido',
      'cerrado' => 'com_cerrado',
      'id_modalidad' => 'mod_id',
  ];

  protected $appends = [
      'id',
      'id_usuario',
      'id_carrera',
      'id_materia',
      'id_sede',
      'anio',
      'numero',
      'alumnos_cantidad',
      'responsable_nombre',
      'responsable_apellido',
      'cerrado',
      'id_modalidad',
  ];

  public function responsable(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function materia(){
    return $this->hasOne('App\Models\Materia','mat_id','mat_id');
  }

  public function carrera(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id');
  }

  public function modalidad(){
    return $this->hasOne('App\Models\Modalidad','mod_id','mod_id');
  }

  public function usuario_alta(){
    return $this->hasOne('App\User','usu_id','usu_id_alta');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function alumnos(){
    return $this->hasMany('App\Models\ComisionAlumno','com_id','com_id');
  }

  public function asistencias(){
    return $this->hasMany('App\Models\Asistencia','com_id','com_id');
  }

  public function examenes(){
    return $this->hasMany('App\Models\Comision\Examen','com_id','com_id');
  }

  public function docentes(){
    return $this->hasMany('App\Models\Comision\Docente','com_id','com_id');
  }
}
