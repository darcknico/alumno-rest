<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoAlumnoEstado extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_alumno_estado';
  protected $primaryKey = 'tae_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tae_id',
    'tae_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tae_id',
      'nombre' => 'tae_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
