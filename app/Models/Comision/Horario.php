<?php
namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Horario extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comision_horario';
  protected $primaryKey = 'cho_id';

  protected $with = [
    'dia',
    'aula',
  ];

  protected $casts = [
      'estado'=>'boolean',
      'cho_asistencia'=>'boolean',
  ];

  protected $hidden = [
    'cho_id',
    'com_id',
    'dia_id',
    'cho_hora_inicial',
    'cho_hora_final',
    'aul_id',
    'cho_nombre',
    'cho_asistencia',
  ];

  protected $maps = [
      'id' => 'cho_id',
      'id_comision' => 'com_id',
      'id_dia' => 'dia_id',
      'hora_inicial' => 'cho_hora_inicial',
      'hora_final' => 'cho_hora_final',
      'id_aula' => 'aul_id',
      'nombre' => 'cho_nombre',
      'asistencia' => 'cho_asistencia',
  ];

  protected $appends = [
      'id',
      'id_comision',
      'id_dia',
      'hora_inicial',
      'hora_final',
      'id_aula',
      'nombre',
      'asistencia',
  ];


  public function dia(){
    return $this->hasOne('App\Models\Extra\Dia','dia_id','dia_id');
  }

  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }

  public function aula(){
    return $this->hasOne('App\Models\Academico\Aula','aul_id','aul_id');
  }

}
