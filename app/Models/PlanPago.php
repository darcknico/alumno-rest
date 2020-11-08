<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use App\Models\Obligacion;

use Carbon\Carbon;

class PlanPago extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_planes_pago';
  protected $primaryKey = 'ppa_id';

  protected $casts = [
      'estado'=>'boolean',
      'ppa_cuota_cantidad'=>'integer',
      'ppa_matricula_original_monto'=>'float',
      'ppa_cuota_original_monto'=>'float',
      'ppa_matricula_monto'=>'float',
      'ppa_matricula_saldo'=>'float',
      'ppa_matricula_pagado'=>'float',
      'ppa_cuota_monto'=>'float',
      'ppa_interes_monto'=>'float',
  ];

  protected $hidden = [
    'ppa_id',
    'ins_id',
    'sed_id',
    'bec_id',
    'ppp_id',
    'ppa_matricula_original_monto',
    'ppa_cuota_original_monto',
    'ppa_matricula_monto',
    'ppa_matricula_saldo',
    'ppa_matricula_pagado',
    'ppa_cuota_monto',
    'ppa_interes_monto',
    'ppa_anio',
    'ppa_cuota_total',
    'ppa_cuota_cantidad',
    'ppa_cuota_pagado',
    'ppa_dias_vencimiento',
    'ppa_fecha',
    'usu_id',
    'usu_id_baja',
  ];

  protected $maps = [
    'id' => 'ppa_id',
    'id_sede' => 'sed_id',
    'id_inscripcion' => 'ins_id',
    'id_beca' => 'bec_id',
    'id_plan_pago_precio' => 'ppp_id',
    'matricula_original_monto' => 'ppa_matricula_original_monto',
    'cuota_original_monto' => 'ppa_cuota_original_monto',
    'id_inscripcion' => 'ins_id',
    'matricula_monto' => 'ppa_matricula_monto',
    'matricula_saldo' => 'ppa_matricula_saldo',
    'matricula_pagado' => 'ppa_matricula_pagado',
    'cuota_monto' => 'ppa_cuota_monto',
    'interes_monto' => 'ppa_interes_monto',
    'anio'=>'ppa_anio',
    'cuota_total'=>'ppa_cuota_total',
    'cuota_cantidad'=>'ppa_cuota_cantidad',
    'cuota_pagado'=>'ppa_cuota_pagado',
    'dias_vencimiento' => 'ppa_dias_vencimiento',
    'fecha' => 'ppa_fecha',
    'id_usuario' => 'usu_id',
    'id_usuario_baja' => 'usu_id_baja',
  ];

  protected $appends = [
    'id',
    'id_inscripcion',
    'id_sede',
    'id_beca',
    'id_plan_pago_precio',
    'matricula_original_monto',
    'cuota_original_monto',
    'matricula_monto',
    'matricula_saldo',
    'matricula_pagado',
    'cuota_monto',
    'interes_monto',
    'anio',
    'cuota_total',
    'cuota_cantidad',
    'cuota_pagado',
    'dias_vencimiento',
    'fecha',
    'id_usuario',

    'pagado',
    'bonificado',
    'interes_total',
    'interes_saldo',
    'cuota_total',
    'saldo_total',
    'saldo_hoy',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function inscripcion(){
    return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function obligaciones(){
    return $this->hasMany('App\Models\Obligacion','ppa_id','ppa_id');
  }

  public function pagos(){
    return $this->hasMany('App\Models\Pago','ppa_id','ppa_id');
  }


  public function getPagadoAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
      'ppa_id' => $this['id'],
      'estado' => 1,
    ])
    ->whereIn('tob_id',[3,4])
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }

  public function getBonificadoAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
      'ppa_id' => $this['id'],
      'estado' => 1,
    ])
    ->where('tob_id',4)
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }

  public function getInteresTotalAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_monto) as total')->where([
      'ppa_id' => $this['id'],
      'estado' => 1,
    ])
    ->where('tob_id',2)
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }

  public function getInteresSaldoAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_saldo) as total')->where([
      'ppa_id' => $this['id'],
      'estado' => 1,
    ])
    ->where('tob_id',2)
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }

  public function getSaldoTotalAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,sum(obl_saldo) as total')->where([
      'ppa_id' => $this['id'],
      'estado' => 1,
    ])
    ->whereIn('tob_id',[1,2])
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }

  public function getSaldoHoyAttribute(){
    $obligacion = Obligacion::selectRaw('ppa_id,(sum( IF(tob_id = 1 OR tob_id = 2,obl_monto,0)) - sum( IF(tob_id = 3 OR tob_id = 4,obl_monto,0))) as total')
    ->where([
      'ppa_id' => $this['ppa_id'],
      'estado' => 1,
    ])
    ->where('obl_fecha_vencimiento','<=',Carbon::now())
    ->groupBy('ppa_id')->first();
    return $obligacion['total']??0;
  }
}
