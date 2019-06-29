<?php

namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Examen extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comision_examen';
  protected $primaryKey = 'cex_id';

  protected $with = [
    'tipo',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'cex_id',
    'com_id',
    'tex_id',
    'cex_nombre',
    'cex_observaciones',
    'cex_fecha',

    'usu_id',
    'usu_id_baja',
    'deleted_at',
  ];

  protected $maps = [
      'id' => 'cex_id',
      'id_comision' => 'com_id',
      'id_tipo_examen' => 'tex_id',

      'nombre' => 'cex_nombre',
      'observaciones' => 'cex_observaciones',
      'fecha' => 'cex_fecha',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'id_comision',
      'id_tipo_examen',
      'nombre',
      'observaciones',
      'fecha',
      'id_usuario',

  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }


  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\Comision\TipoExamen','tex_id','tex_id');
  }

  public function alumnos(){
    return $this->hasMany('App\Models\Comision\ExamenAlumno','cex_id','cex_id');
  }



  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }


}
