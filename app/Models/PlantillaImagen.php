<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class PlantillaImagen extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_plantilla_imagen';
  protected $primaryKey = 'pim_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'pim_id',
    'pim_nombre',
    'pim_dir',
    'sed_id',
    'usu_id',
  ];

  protected $maps = [
    'id' => 'pim_id',
    'nombre' => 'pim_nombre',
    'id_usuario' => 'usu_id',
    'id_sede' => 'sed_id',
  ];

  protected $appends = [
    'id',
    'nombre',
    'id_usuario',
    'id_sede',
    'url',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function getUrlAttribute(){
    return url('/').'/'.$this['pim_dir'];
  }
}
