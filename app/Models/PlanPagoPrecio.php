<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use App\Models\Obligacion;

use Carbon\Carbon;

class PlanPagoPrecio extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_plan_pago_precio';
  protected $primaryKey = 'ppp_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'ppp_id',
    'ppp_matricula_monto',
    'ppp_cuota_monto',
    'ppp_bonificacion_monto',
    'ppp_interes_monto',
    'sed_id',
    'usu_id',
    'usu_id_baja',
  ];

  protected $maps = [
    'id' => 'ppp_id',
    'matricula_monto' => 'ppp_matricula_monto',
    'cuota_monto' => 'ppp_cuota_monto',
    'bonificacion_monto' => 'ppp_bonificacion_monto',
    'interes_monto' => 'ppp_interes_monto',
    'id_sede' => 'sed_id',
    'id_usuario' => 'usu_id',
    'id_usuario_baja' => 'usu_id_baja',
  ];

  protected $appends = [
    'id',
    'matricula_monto',
    'cuota_monto',
    'bonificacion_monto',
    'interes_monto',
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

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }


}
