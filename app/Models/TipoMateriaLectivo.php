<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoMateriaLectivo extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_materia_lectivo';
  protected $primaryKey = 'tml_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tml_id',
    'tml_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tml_id',
      'nombre' => 'tml_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
