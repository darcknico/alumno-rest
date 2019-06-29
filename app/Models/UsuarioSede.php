<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class UsuarioSede extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_usuario_sede';
  protected $primaryKey = 'use_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'use_id',
    'usu_id',
    'sed_id',
    'usu_id_usuario',
  ];

  protected $maps = [
      'id' => 'use_id',
      'id_usuario' => 'usu_id',
      'id_sede' => 'sed_id',
      'id_usuario_alta' => 'usu_id_usuario',
  ];

  protected $appends = [
      'id',
      'id_usuario',
      'id_sede',
      'id_usuario_alta',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_alta(){
    return $this->hasOne('App\User','usu_id','usu_id_usuario');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }
}
