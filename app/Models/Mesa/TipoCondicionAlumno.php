<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoCondicionAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_condicion_alumno';
  protected $primaryKey = 'tca_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tca_id',
    'tca_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tca_id',
      'nombre' => 'tca_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
