<?php

namespace App\Models\Extra;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class ReporteJob extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_reporte_job';
  protected $primaryKey = 'rjo_id';

  protected $with = [
    'usuario',
  ];

  protected $hidden = [
    'rjo_id',
    'rjo_cantidad',
    'rjo_contador',
    'rjo_ruta',
    'rjo_dir',
    'rjo_nombre',
    'rjo_terminado',
    'estado',
    'usu_id',
    'sed_id',
  ];

  protected $maps = [
    'id' => 'rjo_id',
    'cantidad' => 'rjo_cantidad',
    'contador' => 'rjo_contador',
    'ruta' => 'rjo_ruta',
    'nombre' => 'rjo_nombre',
    'terminado' => 'rjo_terminado',
    'id_usuario' => 'usu_id',
    'id_sede' => 'sed_id',
  ];

  protected $appends = [
    'id',
    'cantidad',
    'contador',
    'ruta',
    'nombre',
    'terminado',
    'id_usuario',
    'id_sede',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }
}