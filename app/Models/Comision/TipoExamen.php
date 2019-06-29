<?php

namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoExamen extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_examen';
  protected $primaryKey = 'tex_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tex_id',
    'tex_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tex_id',
      'nombre' => 'tex_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
