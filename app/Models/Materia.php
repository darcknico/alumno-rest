<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Materia extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_materias';
  protected $primaryKey = 'mat_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mat_id',
    'pes_id',
    'mat_nombre',
    'mat_codigo',
    'mat_horas',
    'tmr_id',
    'tml_id',
    'usu_id',
    'mat_aula_virtual_id',
    'mat_examen_virtual_id',
  ];

  protected $maps = [
      'id' => 'mat_id',
      'nombre' => 'mat_nombre',
      'codigo' => 'mat_codigo',
      'horas' => 'mat_horas',
      'id_tipo_materia_regimen' => 'tmr_id',
      'id_tipo_materia_lectivo' => 'tml_id',
      'id_plan_estudio' => 'pes_id',
      'id_usuario' => 'usu_id',
      'id_aula_virtual' => 'mat_aula_virtual_id',
      'id_examen_virtual' => 'mat_examen_virtual_id',
  ];

  protected $appends = [
      'id',
      'nombre',
      'codigo',
      'horas',
      'id_tipo_materia_regimen',
      'id_tipo_materia_lectivo',
      'id_plan_estudio',
      'id_usuario',
      'id_aula_virtual',
      'id_examen_virtual',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
  
  public function planEstudio(){
    return $this->hasOne('App\Models\PlanEstudio','pes_id','pes_id');
  }

  public function tipoRegimen(){
    return $this->hasOne('App\Models\TipoMateriaRegimen','tmr_id','tmr_id');
  }

  public function tipoLectivo(){
    return $this->hasOne('App\Models\TipoMateriaLectivo','tml_id','tml_id');
  }

  public function correlatividades(){
    return $this->hasMany('App\Models\MateriaCorrelatividad','mat_id','mat_id');
  }

  public function mesas_examenes(){
    return $this->hasMany('App\Models\Mesa\MesaExamenMateria','mat_id','mat_id');
  }

  public function comisiones(){
    return $this->hasMany('App\Models\Comision','mat_id','mat_id');
  }
}
