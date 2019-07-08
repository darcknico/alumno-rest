<?php

namespace App\Models\Novedad;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Sistema extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_novedades_sistema';
  protected $primaryKey = 'nsi_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'nsi_id',
    'nsi_titulo',
    'nsi_descripcion',
    'nsi_cuerpo',
    'usu_id',
    'nsi_mostrar',
  ];

  protected $maps = [
      'id' => 'nsi_id',
      'titulo' => 'nsi_titulo',
      'descripcion' => 'nsi_descripcion',
      'id_usuario' => 'usu_id',
      'mostrar' => 'nsi_mostrar',
  ];

  protected $appends = [
      'id',
      'titulo',
      'descripcion',
      'id_usuario',
      'mostrar',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuarios(){
    return $this->hasMany('App\Models\Novedad\Usuario','nsi_id','nsi_id');
  }

}
