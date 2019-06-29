<?php

namespace App\Models\Tipos;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoContrato extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_contratos';
  protected $primaryKey = 'tco_id';

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
