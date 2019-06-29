<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AlumnoArchivo extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_alumno_archivo';
  protected $primaryKey = 'aar_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'aar_id',
    'aar_nombre',
    'aar_dir',
    'tad_id',
    'alu_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'aar_id',
      'nombre' => 'aar_nombre',
      'id_tipo_alumno_documentacion' => 'tad_id',
      'id_alumno' => 'alu_id',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'id_tipo_alumno_documentacion',
      'id_alumno',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function tipo_documentacion(){
    return $this->hasOne('App\Models\TipoAlumnoDocumentacion','tad_id','tad_id');
  }

}
