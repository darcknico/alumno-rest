<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoPago extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_pago';
  protected $primaryKey = 'tpa_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tpa_id',
    'tpa_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tpa_id',
      'nombre' => 'tpa_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
