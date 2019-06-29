<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Sede extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_sedes';
  protected $primaryKey = 'sed_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'sed_id',
    'sed_nombre',
    'loc_id',
    'sed_localidad',
    'sed_ubicacion',
    'sed_latitud',
    'sed_longitud',
    'sed_codigo_postal',
    'sed_direccion',
    'sed_telefono',
    'sed_celular',
    'sed_email',
    'sed_punto_venta',
    'sed_pago_numero',
    'sed_mesa_numero',
    'usu_id',
    'sed_room_id',
  ];

  protected $maps = [
      'id' => 'sed_id',
      'nombre' => 'sed_nombre',
      'ubicacion' => 'sed_ubicacion',
      'localidad' => 'sed_localidad',
      'latitud' => 'sed_latitud',
      'longitud' => 'sed_longitud',
      'codigo_postal' => 'sed_codigo_postal',
      'direccion' => 'sed_direccion',
      'telefono' => 'sed_telefono',
      'celular' => 'sed_celular',
      'email' => 'sed_email',
      'punto_venta' => 'sed_punto_venta',
      'pago_numero' => 'sed_pago_numero',
      'mesa_numero' => 'sed_mesa_numero',
      'id_localidad' => 'loc_id',
      'id_usuario' => 'usu_id',
      'room_id' => 'sed_room_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'ubicacion',
      'localidad',
      'latitud',
      'longitud',
      'codigo_postal',
      'direccion',
      'telefono',
      'celular',
      'email',
      'punto_venta',
      'pago_numero',
      'mesa_numero',
      'id_localidad',
      'id_usuario',
      'room_id',
  ];

  public function departamentos(){
    return $this->hasMany('App\Models\Departamento','sed_id','sed_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
}
