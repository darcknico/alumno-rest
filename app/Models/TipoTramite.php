<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoTramite extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_tramite';
  protected $primaryKey = 'ttr_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'ttr_id',
    'ttr_nombre',
    'ttr_monto',
    'estado',
  ];

  protected $maps = [
      'id' => 'ttr_id',
      'nombre' => 'ttr_nombre',
      'monto' => 'ttr_monto',
  ];

  protected $appends = [
    'id',
    'nombre',
    'monto',
  ];

}
