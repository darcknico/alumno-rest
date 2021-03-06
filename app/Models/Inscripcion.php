<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Inscripcion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_inscripciones';
  protected $primaryKey = 'ins_id';

  protected $with = [
    'beca',
    'tipo_estado',
    'modalidad',
    'periodo',
    'sede:id,nombre',
  ];

  protected $casts = [
      'estado'=>'boolean',
      'ins_porcentaje_aprobados' => 'double',
  ];

  protected $hidden = [
    'ins_id',
    'sed_id',
    'alu_id',
    'car_id',
    'pes_id',
    'mod_id',
    'usu_id',
    'tie_id',
    'ins_anio',
    'bec_id',
    'bec_nombre',
    'bec_porcentaje',
    'car_id_tecnicatura',
    'ins_observaciones',
    'ins_fecha_egreso',
    'tml_id',
    'ins_porcentaje_aprobados',
    'ins_final_total',
    'ins_final_total_aprobados',
    'ins_final_promedio',
    'ins_final_promedio_aprobados',
  ];

  protected $maps = [
    'id' => 'ins_id',
    'id_sede' => 'sed_id',
    'id_alumno' => 'alu_id',
    'id_carrera' => 'car_id',
    'id_plan_estudio' => 'pes_id',
    'id_tipo_inscripcion_estado' => 'tie_id',
    'id_modalidad' => 'mod_id',
    'id_usuario' => 'usu_id',
    'anio' => 'ins_anio',
    'id_beca' => 'bec_id',
    'beca_nombre' => 'bec_nombre',
    'beca_porcentaje' => 'bec_porcentaje',
    'id_tecnicatura' => 'car_id_tecnicatura',
    'observaciones' => 'ins_observaciones',
    'id_periodo_lectivo' => 'tml_id',
    'fecha_egreso' => 'ins_fecha_egreso',
    'porcentaje_aprobados' => 'ins_porcentaje_aprobados',
    'final_total' => 'ins_final_total',
    'final_total_aprobados' => 'ins_final_total_aprobados',
    'final_promedio' => 'ins_final_promedio',
    'final_promedio_aprobados' => 'ins_final_promedio_aprobados',
  ];

  protected $appends = [
    'id',
    'id_sede',
    'id_alumno',
    'id_carrera',
    'id_plan_estudio',
    'id_tipo_inscripcion_estado',
    'id_modalidad',
    'id_usuario',
    'anio',
    'id_beca',
    'beca_nombre',
    'beca_porcentaje',
    'id_tecnicatura',
    'observaciones',
    'fecha_egreso',
    'id_periodo_lectivo',
    'porcentaje_aprobados',
    'final_total',
    'final_total_aprobados',
    'final_promedio',
    'final_promedio_aprobados',
  ];

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function alumno(){
    return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
  }

  public function beca(){
    return $this->hasOne('App\Models\Beca','bec_id','bec_id');
  }

  public function carrera(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id');
  }

  public function tecnicatura(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id_tecnicatura');
  }

  public function plan_estudio(){
    return $this->hasOne('App\Models\PlanEstudio','pes_id','pes_id');
  }

  public function tipo_estado(){
    return $this->hasOne('App\Models\TipoInscripcionEstado','tie_id','tie_id');
  }
  public function tipo_abandonos(){
    return $this->hasOne('App\Models\Academico\InscripcionEstado','ins_id','ins_id');
  }

  public function modalidad(){
    return $this->hasOne('App\Models\Modalidad','mod_id','mod_id');
  }

  public function periodo(){
    return $this->hasOne('App\Models\TipoMateriaLectivo','tml_id','tml_id');
  }

  public function planes_pago(){
    return $this->hasMany('App\Models\PlanPago','ins_id','ins_id');
  }

  public function comisiones(){
    return $this->hasMany('App\Models\ComisionAlumno','ins_id','ins_id');
  }
}
