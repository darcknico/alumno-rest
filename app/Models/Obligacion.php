<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Obligacion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_obligaciones';
  protected $primaryKey = 'obl_id';

  protected $with = [
    'mercadopago',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'obl_id',
    'ppa_id',
    'usu_id',
    'tob_id',
    'obl_descripcion',
    'obl_monto',
    'obl_saldo',
    'obl_pagado',
    'obl_id_obligacion',
    'obl_fecha_vencimiento',
    'obl_fecha',
  ];

  protected $maps = [
    'id' => 'obl_id',
    'id_plan_pago' => 'ppa_id',
    'id_usuario' => 'usu_id',
    'id_tipo_obligacion' => 'tob_id',
    'descripcion' => 'obl_descripcion',
    'monto' => 'obl_monto',
    'saldo' => 'obl_saldo',
    'pagado' => 'obl_pagado',
    'id_obligacion' => 'obl_id_obligacion',
    'fecha_vencimiento' => 'obl_fecha_vencimiento',
    'fecha' => 'obl_fecha',
  ];

  protected $appends = [
    'id',
    'id_plan_pago',
    'id_usuario',
    'id_tipo_obligacion',
    'descripcion',
    'monto',
    'saldo',
    'pagado',
    'id_obligacion',
    'fecha_vencimiento',
    'fecha',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function plan_pago(){
    return $this->hasOne('App\Models\PlanPago','ppa_id','ppa_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoObligacion','tob_id','tob_id');
  }

  public function obligacion(){
    return $this->hasOne('App\Models\Obligacion','obl_id','obl_id_obligacion');
  }

  public function interes(){
    return $this->hasOne('App\Models\Obligacion','obl_id_obligacion','obl_id');
  }

  public function pagos(){
    return $this->hasMany('App\Models\ObligacionPago','obl_id','obl_id');
  }

  public function intereses(){
    return $this->hasMany('App\Models\ObligacionInteres','obl_id','obl_id');
  }

  public function mercadopago(){
    return $this->hasOne('App\Models\PaymentMercadoPago','obl_id','obl_id');
  }

  public function inscripcion(){
    return $this->hasOneThrough(
      'App\Models\Inscripcion',
      'App\Models\PlanPago',
      'ppa_id', // Foreign key on cars table...
      'ins_id', // Foreign key on owners table...
      'ppa_id', // Local key on mechanics table...
      'ins_id' // Local key on cars table...
    );
  }

}
