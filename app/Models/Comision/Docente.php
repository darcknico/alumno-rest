<?php
namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Docente extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_comision_docente';
  protected $primaryKey = 'cdo_id';

  protected $with = [
    'usuario',
  ];

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'cdo_id',
    'com_id',
    'usu_id',
    'cdo_observaciones'
  ];

  protected $maps = [
      'id' => 'cdo_id',
      'id_comision' => 'com_id',
      'id_usuario' => 'usu_id',
      'observaciones' => 'cdo_observaciones',
  ];

  protected $appends = [
      'id',
      'id_comision',
      'id_usuario',
      'observaciones',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function docente(){
    return $this->hasOne('App\Models\Academico\Docente','usu_id','usu_id');
  }

  public function comision(){
    return $this->hasOne('App\Models\Comision','com_id','com_id');
  }

}
