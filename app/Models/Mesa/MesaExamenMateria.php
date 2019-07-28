<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class MesaExamenMateria extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_mesa_materia';
  protected $primaryKey = 'mma_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mma_id',
    'mes_id',
    'car_id',
    'mat_id',
    'usu_id',
    'mma_fecha',
    'mma_fecha_cierre',
    'mma_alumnos_cantidad',
    'mma_alumnos_cantidad_presente',
    'mma_ubicacion',
    'mma_check_in',
    'mma_check_out',
    'mma_observaciones',
    'mma_alumnos_cantidad_aprobado',
    'mma_alumnos_cantidad_no_aprobado',
    'mma_observaciones',
    'deleted_at',
    'usu_id_baja',
    'usu_id_check_in',
    'usu_id_check_out',
    'mma_libro',
    'mma_folio',
  ];

  protected $maps = [
      'id' => 'mma_id',
      'id_mesa_examen' => 'mes_id',
      'id_carrera' => 'car_id',
      'id_materia' => 'mat_id',
      'id_usuario' => 'usu_id',
      'fecha' => 'mma_fecha',
      'fecha_cierre' => 'mma_fecha_cierre',
      'alumnos_cantidad' => 'mma_alumnos_cantidad',
      'alumnos_cantidad_presente' => 'mma_alumnos_cantidad_presente',
      'alumnos_cantidad_aprobado' => 'mma_alumnos_cantidad_aprobado',
      'alumnos_cantidad_no_aprobado' => 'mma_alumnos_cantidad_no_aprobado',
      'observaciones'=>'mma_observaciones',
      'ubicacion' => 'mma_ubicacion',
      'check_in' => 'mma_check_in',
      'check_out' => 'mma_check_out',
      'libro' => 'mma_libro',
      'folio' => 'mma_folio',
  ];

  protected $appends = [
      'id',
      'id_mesa_examen',
      'id_carrera',
      'id_materia',
      'id_usuario',
      'fecha',
      'fecha_cierre',
      'alumnos_cantidad',
      'alumnos_cantidad_presente',
      'ubicacion',
      'check_in',
      'check_out',
      'observaciones',
      'libro',
      'folio',
      'alumnos_cantidad_aprobado',
      'alumnos_cantidad_no_aprobado',
  ];


  public function mesa_examen(){
    return $this->hasOne('App\Models\Mesa\MesaExamen','mes_id','mes_id');
  }

  public function carrera(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id');
  }

  public function materia(){
    return $this->hasOne('App\Models\Materia','mat_id','mat_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_check_in(){
    return $this->hasOne('App\User','usu_id','usu_id_check_in');
  }

  public function usuario_check_out(){
    return $this->hasOne('App\User','usu_id','usu_id_check_out');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function alumnos(){
    return $this->hasMany('App\Models\Mesa\MesaExamenMateriaAlumno','mma_id','mma_id');
  }

  public function docentes(){
    return $this->hasMany('App\Models\Mesa\MesaExamenMateriaDocente','mma_id','mma_id');
  }
}
