<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Modalidad extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_modalidades';
  protected $primaryKey = 'mod_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mod_id',
    'mod_nombre',
    'mod_descripcion',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'mod_id',
      'nombre' => 'mod_nombre',
      'descripcion' => 'mod_descripcion',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'descripcion',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
}
