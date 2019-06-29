<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoComprobante extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_comprobantes';
  protected $primaryKey = 'tco_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tco_id',
    'tco_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tco_id',
      'nombre' => 'tco_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
