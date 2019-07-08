<?php

namespace App\Models\Novedad;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Usuario extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_novedad_usuario';
  protected $primaryKey = 'nus_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'nus_id',
    'nsi_id',
    'usu_id',
    'nus_visto',

  ];

  protected $maps = [
      'id' => 'nus_id',
      'id_novedad_sistema' => 'nsi_id',
      'id_usuario' => 'usu_id',
      'visto' => 'nus_visto',

  ];

  protected $appends = [
      'id',
      'id_novedad_sistema',
      'id_usuario',
      'visto',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function novedad(){
    return $this->hasOne('App\Models\Novedad\Sistema','nsi_id','nsi_id');
  }

}
