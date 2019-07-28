<?php

namespace App\Models\Mesa;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class MesaExamenMateriaDocente extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_mesa_materia_docente';
  protected $primaryKey = 'mmd_id';

  protected $with = [
    'tipo',
    'docente',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mmd_id',
    'mma_id',
    'usu_id',
    'tmd_id',
    'mmd_observaciones',
  ];

  protected $maps = [
    'id' => 'mmd_id',
    'id_mesa_examen_materia' => 'mma_id',
    'id_usuario' => 'usu_id',
    'id_tipo_mesa_docente' => 'tmd_id',
    'observaciones' => 'mmd_observaciones',
  ];

  protected $appends = [
      'id',
      'id_mesa_examen_materia',
      'id_usuario',
      'id_tipo_mesa_docente',
      'observaciones',
  ];


  public function mesa_examen_materia(){
    return $this->hasOne('App\Models\Mesa\MesaExamenMateria','mma_id','mma_id');
  }

  public function tipo(){
    return $this->hasOne('App\Models\Tipos\TipoMesaDocente','tmd_id','tmd_id');
  }

  public function docente(){
    return $this->hasOne('App\Models\Academico\Docente','usu_id','usu_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }


}
