<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoMateriaRegimen extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_materia_regimen';
  protected $primaryKey = 'tmr_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tmr_id',
    'tmr_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tmr_id',
      'nombre' => 'tmr_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];
}
