<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoObligacion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_obligacion';
  protected $primaryKey = 'tob_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tob_id',
    'tob_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tob_id',
      'nombre' => 'tob_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
