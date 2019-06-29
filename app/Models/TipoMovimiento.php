<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoMovimiento extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_movimiento';
  protected $primaryKey = 'tmo_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tmo_id',
    'tmo_nombre',
    'tmo_descripcion',
    'tei_id',
    'sed_id',
    'usu_id',
    'deleted_at',
    'usu_id_baja',
  ];

  protected $maps = [
      'id' => 'tmo_id',
      'nombre' => 'tmo_nombre',
      'descripcion' => 'tmo_descripcion',
      'id_tipo_egreso_ingreso' => 'tei_id',
      'id_sede' => 'sed_id',
      'id_usuario' => 'usu_id',
      'id_usuario_baja' => 'usu_id_baja',
  ];

  protected $appends = [
      'id',
      'nombre',
      'descripcion',
      'id_tipo_egreso_ingreso',
      'id_sede',
      'id_usuario',
      'id_usuario_baja',
  ];


  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

}
