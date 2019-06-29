<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class UsuarioArchivo extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_usuario_archivo';
  protected $primaryKey = 'uar_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'uar_id',
    'uar_nombre',
    'uar_dir',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'uar_id',
      'nombre' => 'uar_nombre',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

}
