<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Alumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_alumnos';
  protected $primaryKey = 'alu_id';

  protected $with = [
    'tipoDocumento',
    'tipo_civil',
    'tipo_estado',
  ];

  protected $casts = [
      'estado'=>'boolean',
      'fecha_nacimiento'=>'date',
  ];

  protected $hidden = [
    'alu_id',
    'sed_id',
    'alu_nombre',
    'alu_apellido',
    'alu_fecha_alta',
    'alu_codigo',
    'alu_domicilio',
    'alu_calle',
    'alu_numero',
    'alu_piso',
    'alu_depto',
    'loc_id',
    'alu_localidad',
    'pro_id',
    'alu_codigo_postal',
    'alu_telefono',
    'alu_celular',
    'alu_email',
    'tdo_id',
    'alu_documento',
    'alu_fecha_nacimiento',
    'alu_ciudad_nacimiento',
    'alu_nacionalidad',
    'alu_sexo',
    'tac_id',
    'tae_id',
    'alu_observaciones',
    'deleted_at',
    'usu_id',
    'alu_password',
    'usu_id_baja',
  ];

  protected $maps = [
      'id' => 'alu_id',
      'nombre' => 'alu_nombre',
      'apellido' => 'alu_apellido',
      'fecha_alta' => 'alu_fecha_alta',
      'codigo' => 'alu_codigo',
      'domicilio' => 'alu_domicilio',
      'calle' => 'alu_calle',
      'numero' => 'alu_numero',
      'piso' => 'alu_piso',
      'depto' => 'alu_depto',
      'id_localidad' => 'loc_id',
      'localidad' => 'alu_localidad',
      'id_provincia' => 'pro_id',
      'codigo_postal' => 'alu_codigo_postal',
      'telefono' => 'alu_telefono',
      'celular' => 'alu_celular',
      'email' => 'alu_email',
      'id_tipo_documento' => 'tdo_id',
      'documento' => 'alu_documento',
      'fecha_nacimiento' => 'alu_fecha_nacimiento',
      'ciudad_nacimiento' => 'alu_ciudad_nacimiento',
      'nacionalidad' => 'alu_nacionalidad',
      'sexo' => 'alu_sexo',
      'id_tipo_alumno_civil' => 'tac_id',
      'id_tipo_alumno_estado' => 'tae_id',
      'observaciones' => 'alu_observaciones',
      'id_usuario' => 'usu_id',
      'id_usuario_baja' => 'usu_id_baja',
  ];

  protected $appends = [
      'id',
      'nombre',
      'apellido',
      'fecha_alta',
      'codigo',
      'domicilio',
      'calle',
      'numero',
      'piso',
      'depto',
      'id_localidad',
      'localidad',
      'id_provincia',
      'codigo_postal',
      'telefono',
      'celular',
      'email',
      'id_tipo_documento',
      'documento',
      'fecha_nacimiento',
      'ciudad_nacimiento',
      'nacionalidad',
      'sexo',
      'id_tipo_alumno_civil',
      'id_tipo_alumno_estado',
      'observaciones',
      'id_usuario',
      'id_usuario_baja',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function tipoDocumento(){
    return $this->hasOne('App\Models\TipoDocumento','tdo_id','tdo_id');
  }

  public function tipo_civil(){
    return $this->hasOne('App\Models\TipoAlumnoCivil','tac_id','tac_id');
  }

  public function tipo_estado(){
    return $this->hasOne('App\Models\TipoAlumnoEstado','tae_id','tae_id');
  }

  public function provincia(){
    return $this->hasOne('App\Models\Extra\Provincia','pro_id','pro_id');
  }

  public function inscripciones(){
    return $this->hasMany('App\Models\Inscripcion','alu_id','alu_id');
  }

  public function asistencias(){
    return $this->hasMany('App\Models\Asistencia','alu_id','alu_id');
  }

  public function archivos(){
    return $this->hasMany('App\Models\AlumnoArchivo','alu_id','alu_id');
  }

  public function notificaciones(){
    return $this->hasMany('App\Models\AlumnoNotificacion','alu_id','alu_id');
  }

}
