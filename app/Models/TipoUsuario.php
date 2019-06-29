<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoUsuario extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_usuarios';
  protected $primaryKey = 'tus_id';

  protected $hidden = [
    'tus_id',
    'tus_nombre',
    'tus_descripcion',
    'estado',
  ];

  protected $maps = [
    'id' => 'tus_id',
    'nombre' => 'tus_nombre',
    'descripcion' => 'tus_descripcion',
  ];

  protected $appends = [
    'id',
    'nombre',
    'descripcion',
  ];

  public function tipo(){
    return $this->hasOne('App\Models\TipoUsuario','tus_id','tus_id');
  }
  
  public function tipoDocumento(){
    return $this->hasOne('App\Models\TipoDocumento','tdo_id','tdo_id');
  }

}
