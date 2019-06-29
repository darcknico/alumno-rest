<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Beca extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_becas';
  protected $primaryKey = 'bec_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'bec_id',
    'bec_nombre',
    'bec_descripcion',
    'bec_porcentaje',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'bec_id',
      'nombre' => 'bec_nombre',
      'descripcion' => 'bec_descripcion',
      'porcentaje' => 'bec_porcentaje',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'descripcion',
      'porcentaje',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

}
