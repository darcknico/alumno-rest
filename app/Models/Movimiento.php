<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

use App\Models\Plantilla;
use App\Models\Alumno;
use App\Models\AlumnoNotificacion;

class Movimiento extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_movimientos';
  protected $primaryKey = 'mov_id';
  protected $with = [
    'forma',
    'tipo',
    'tipo_comprobante',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mov_id',
    'mov_monto',
    'mov_fecha',
    'mov_cheque_numero',
    'mov_cheque_banco',
    'mov_cheque_origen',
    'mov_cheque_vencimiento',
    'mov_descripcion',
    'mov_numero',
    'mov_numero_transaccion',
    'tco_id',
    'fpa_id',
    'tmo_id',
    'usu_id',
    'sed_id',
    'tei_id',
    'deleted_at',
    'usu_id_baja',
  ];

  protected $maps = [
    'id' => 'mov_id',
    'monto' => 'mov_monto',
    'fecha' => 'mov_fecha',
    'cheque_numero' => 'mov_cheque_numero',
    'cheque_banco' => 'mov_cheque_banco',
    'cheque_origen' => 'mov_cheque_origen',
    'cheque_vencimiento' => 'mov_cheque_vencimiento',
    'descripcion' => 'mov_descripcion',
    'numero' => 'mov_numero',
    'numero_transaccion' => 'mov_numero_transaccion',
    'id_tipo_comprobante' => 'tco_id',
    'id_forma_pago' => 'fpa_id',
    'id_tipo_movimiento' => 'tmo_id',
    'id_usuario' => 'usu_id',
    'id_usuario_baja' => 'usu_id_baja',
    'id_sede' => 'sed_id',
    'id_tipo_egreso_ingreso' => 'tei_id',
  ];

  protected $appends = [
    'id',
    'monto',
    'fecha',
    'cheque_numero',
    'cheque_banco',
    'cheque_origen',
    'cheque_vencimiento',
    'descripcion',
    'numero',
    'numero_transaccion',
    'id_tipo_comprobante',
    'id_forma_pago',
    'id_tipo_movimiento',
    'id_usuario',
    'id_usuario_baja',
    'id_sede',
    'id_tipo_egreso_ingreso',
  ];


  public function forma(){
    return $this->hasOne('App\Models\FormaPago','fpa_id','fpa_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoMovimiento','tmo_id','tmo_id');
  }

  public function tipo_comprobante(){
    return $this->hasOne('App\Models\TipoComprobante','tco_id','tco_id');
  }

  public function pago(){
    return $this->hasOne('App\Models\Pago','mov_id','mov_id');
  }

}
