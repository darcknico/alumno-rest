<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Pago extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_pagos';
  protected $primaryKey = 'pag_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'pag_id',
    'sed_id',
    'ppa_id',
    'usu_id',
    'obl_id',
    'pag_fecha',
    'pag_monto',
    'pag_descripcion',
    'pag_numero',
    'pag_numero_oficial',
    'tpa_id',
    'mov_id',
    'ins_id',
  ];

  protected $maps = [
    'id' => 'pag_id',
    'id_sede' => 'sed_id',
    'id_plan_pago' => 'ppa_id',
    'id_usuario' => 'usu_id',
    'id_obligacion' => 'obl_id',
    'fecha' => 'pag_fecha',
    'monto' => 'pag_monto',
    'descripcion' => 'pag_descripcion',
    'numero' => 'pag_numero',
    'numero_oficial' => 'pag_numero_oficial',
    'id_tipo_pago' => 'tpa_id',
    'id_movimiento' => 'mov_id',
    'id_inscripcion' => 'ins_id',
  ];

  protected $appends = [
    'id',
    'id_sede',
    'id_plan_pago',
    'id_usuario',
    'id_obligacion',
    'descripcion',
    'monto',
    'fecha',
    'numero',
    'numero_oficial',
    'id_tipo_pago',
    'id_movimiento',
    'id_inscripcion',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function plan_pago(){
    return $this->hasOne('App\Models\PlanPago','ppa_id','ppa_id');
  }

  public function obligacion(){
    return $this->hasOne('App\Models\Obligacion','obl_id','obl_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoPago','tpa_id','tpa_id');
  }

  public function movimiento(){
    return $this->hasOne('App\Models\Movimiento','mov_id','mov_id');
  }

  public function inscripcion(){
    return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
  }

  public function detalles(){
    return $this->hasMany('App\Models\ObligacionPago','pag_id','pag_id');
  }
}
