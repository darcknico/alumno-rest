<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoAsistenciaAlumno extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_asistencia_alumno';
  protected $primaryKey = 'taa_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'taa_id',
    'taa_nombre',
    'taa_descripcion',
    'estado',
  ];

  protected $maps = [
      'id' => 'taa_id',
      'nombre' => 'taa_nombre',
      'descripcion' => 'taa_descripcion',
  ];

  protected $appends = [
    'id',
    'nombre',
    'descripcion',
  ];

}
