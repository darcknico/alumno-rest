<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AlumnoNotificacion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_alumno_notificacion';
  protected $primaryKey = 'ano_id';

  protected $casts = [
      'estado'=>'boolean',
      'ano_enviado'=>'boolean',
  ];

  protected $hidden = [
    'ano_id',
    'alu_id',
    'not_id',
    'usu_id',
    'ano_enviado',
    'ano_visto',
    'ano_token',
    'ano_email',
  ];

  protected $maps = [
      'id' => 'ano_id',
      'enviado' => 'ano_enviado',
      'visto' => 'ano_visto',
      'email' => 'ano_email',
      'id_notificacion' => 'not_id',
      'id_alumno' => 'alu_id',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'id_alumno',
      'id_notificacion',
      'id_usuario',
      'enviado',
      'visto',
      'email',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

}
