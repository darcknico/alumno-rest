<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Plantilla extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_plantillas';
  protected $primaryKey = 'pla_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'pla_id',
    'pla_titulo',
    'pla_descripcion',
    'pla_cuerpo',
    'sed_id',
    'usu_id',
  ];

  protected $maps = [
    'id' => 'pla_id',
    'titulo' => 'pla_titulo',
    'descripcion' => 'pla_descripcion',
    'cuerpo' => 'pla_cuerpo',
    'id_sede' => 'sed_id',
    'id_usuario' => 'usu_id',
  ];

  protected $appends = [
    'id',
    'titulo',
    'descripcion',
    'cuerpo',
    'id_sede',
    'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function archivos(){
    return $this->hasMany('App\Models\PlantillaArchivo','pla_id','pla_id');
  }
}
