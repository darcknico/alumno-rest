<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ObligacionPago extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_obligacion_pago';
  protected $primaryKey = 'opa_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'opa_id',
    'pag_id',
    'obl_id',
    'usu_id',
    'opa_monto',
  ];

  protected $maps = [
    'id' => 'opa_id',
    'id_pago' => 'pag_id',
    'id_obligacion' => 'obl_id',
    'id_usuario' => 'usu_id',
    'monto' => 'opa_monto',
  ];

  protected $appends = [
    'id',
    'id_pago',
    'id_obligacion',
    'id_usuario',
    'monto',
  ];

  public function pago(){
    return $this->hasOne('App\Models\Pago','pag_id','pag_id');
  }

  public function obligacion(){
    return $this->hasOne('App\Models\Obligacion','obl_id','obl_id');
  }

  public function usuario(){
    return $this->hasOne('App\Models\Usuario','usu_id','usu_id');
  }
}
