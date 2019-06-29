<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class MesaExamen extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_mesas_examen';
  protected $primaryKey = 'mes_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mes_id',
    'sed_id',
    'usu_id',
    'mes_fecha_inicio',
    'mes_fecha_fin',
    'mes_numero',
    'mes_nombre',
    'deleted_at',
    'usu_id_baja',
  ];

  protected $maps = [
      'id' => 'mes_id',
      'id_sede' => 'sed_id',
      'id_usuario' => 'usu_id',
      'fecha_inicio' => 'mes_fecha_inicio',
      'fecha_fin' => 'mes_fecha_fin',
      'numero' => 'mes_numero',
      'nombre'=>'mes_nombre',
  ];

  protected $appends = [
      'id',
      'id_sede',
      'id_usuario',
      'fecha_inicio',
      'fecha_fin',
      'numero',
      'nombre',
  ];


  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function materias(){
    return $this->hasMany('App\Models\Mesa\MesaExamenMateria','mes_id','mes_id');
  }

}
