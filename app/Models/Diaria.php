<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

use App\Models\Plantilla;
use App\Models\Alumno;
use App\Models\AlumnoNotificacion;

class Diaria extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_diarias';
  protected $primaryKey = 'dia_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $with = [
    'usuario',
  ];

  protected $hidden = [
    'dia_id',
    'dia_fecha_inicio',
    'dia_fecha_fin',
    'dia_saldo_anterior',
    'dia_saldo_otros_anterior',
    'dia_total_ingreso',
    'dia_total_otros_ingreso',
    'dia_total_egreso',
    'dia_total_otros_egreso',
    'dia_saldo',
    'dia_saldo_otros',
    'sed_id',
    'usu_id',
    'usu_id_cierre',
  ];

  protected $maps = [
    'id' => 'dia_id',
    'fecha_inicio' => 'dia_fecha_inicio',
    'fecha_fin' => 'dia_fecha_fin',
    'saldo_anterior' => 'dia_saldo_anterior',
    'saldo_otros_anterior' => 'dia_saldo_otros_anterior',
    'total_ingreso' => 'dia_total_ingreso',
    'total_otros_ingreso' => 'dia_total_otros_ingreso',
    'total_egreso' => 'dia_total_egreso',
    'total_otros_egreso' => 'dia_total_otros_egreso',
    'saldo' => 'dia_saldo',
    'saldo_otros' => 'dia_saldo_otros',
    'id_sede' => 'sed_id',
    'id_usuario' => 'usu_id',
    'cierre_id_usuario' => 'usu_id_cierre',
  ];

  protected $appends = [
    'id',
    'fecha_inicio',
    'fecha_fin',
    'saldo_anterior',
    'saldo_otros_anterior',
    'total_ingreso',
    'total_otros_ingreso',
    'total_egreso',
    'total_otros_egreso',
    'saldo',
    'saldo_otros',
    'id_sede',
    'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function cierre_usuario(){
    return $this->hasOne('App\User','usu_id','usu_id_cierre');
  }
}
