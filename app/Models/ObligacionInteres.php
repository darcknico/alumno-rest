<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ObligacionInteres extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_obligacion_interes';
  protected $primaryKey = 'oin_id';

  protected $fillable = [
      'estado',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $dates = [
    'created_at',
    'updated_at',
  ]; 

  protected $hidden = [
    'oin_id',
    'oin_fecha',
    'oin_fecha_hasta',
    'oin_saldo',
    'oin_interes',
    'oin_monto',
    'oin_cantidad_meses',
  ];

  protected $maps = [
      'id' => 'oin_id',
      'fecha' => 'oin_fecha',
      'fecha_hasta' => 'oin_fecha_hasta',
      'saldo' => 'oin_saldo',
      'interes' => 'oin_interes',
      'monto' => 'oin_monto',
      'cantidad_meses' => 'oin_cantidad_meses',
      'id_obligacion' => 'obl_id',
  ];

  protected $appends = [
      'id',
      'fecha',
      'fecha_hasta',
      'saldo',
      'interes',
      'monto',
      'cantidad_meses',
      'id_obligacion',
  ];
}