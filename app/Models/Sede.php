<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

/**
 * @OA\Schema(
 *   schema="Sede",
 *   type="object",
 *   required={"nombre"},
 * )
 * Class Sede
 * @package App\Models
 */
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
    'sed_mercadopago',
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
      'mercadopago' => 'sed_mercadopago',
  ];

  /**
   * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="nombre",type="string",maxLength=255)
   * @OA\Property(property="ubicacion",type="string",maxLength=255)
   * @OA\Property(property="latitud",type="number", format="float")
   * @OA\Property(property="longitud",type="number", format="float")
   * @OA\Property(property="codigo_postal",type="integer", format="int64")
   * @OA\Property(property="direccion",type="string",maxLength=255)
   * @OA\Property(property="localidad",type="string",maxLength=255)
   * @OA\Property(property="telefono",type="string",maxLength=255)
   * @OA\Property(property="celular",type="string",maxLength=255)
   * @OA\Property(property="email",type="string",maxLength=255,format="email")
   * @OA\Property(property="id_localidad",type="integer", format="int64")
   * @OA\Property(property="punto_venta",type="integer", format="int64")
   * @OA\Property(property="pago_numero",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="mesa_numero",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="room_id",type="integer", format="int64", readOnly=true, description="Identificacion de la sala en Pusher ChatKit")
   */

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
      'mercadopago',
  ];

  public function departamentos(){
    return $this->hasMany('App\Models\Departamento','sed_id','sed_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
}
