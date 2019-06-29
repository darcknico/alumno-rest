<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class PlantillaArchivo extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_plantilla_archivo';
  protected $primaryKey = 'par_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'par_id',
    'par_nombre',
    'par_dir',
    'pla_id',
    'usu_id',
  ];

  protected $maps = [
    'id' => 'par_id',
    'nombre' => 'par_nombre',
    'id_usuario' => 'usu_id',
    'id_plantilla' => 'pla_id',
  ];

  protected $appends = [
    'id',
    'nombre',
    'id_usuario',
    'id_plantilla',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
  public function plantilla(){
    return $this->hasOne('App\Models\Plantilla','pla_id','pla_id');
  }
}
