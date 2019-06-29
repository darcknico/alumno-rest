<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoAlumnoCivil extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_alumno_civil';
  protected $primaryKey = 'tac_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tac_id',
    'tac_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tac_id',
      'nombre' => 'tac_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
