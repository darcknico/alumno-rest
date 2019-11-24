<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoAlumnoDocumentacion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_alumno_documentacion';
  protected $primaryKey = 'taD_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tad_id',
    'tad_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tad_id',
      'nombre' => 'tad_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

  public function alumnos(){
    return $this->hasMany('App\Models\AlumnoArchivo','tad_id','tad_id');
  }

}
