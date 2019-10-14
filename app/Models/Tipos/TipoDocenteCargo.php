<?php

namespace App\Models\Tipos;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoDocenteCargo extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_docente_cargo';
  protected $primaryKey = 'tdc_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tdc_id',
    'tdc_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tdc_id',
      'nombre' => 'tdc_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
