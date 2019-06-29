<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoInscripcionEstado extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_inscripcion_estado';
  protected $primaryKey = 'tie_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tie_id',
    'tie_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tie_id',
      'nombre' => 'tie_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
