<?php

namespace App\Models\Tipos;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoInscripcionAbandono extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_inscripcion_abandono';
  protected $primaryKey = 'tia_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tia_id',
    'tia_nombre',
    'tia_descripcion',
    'usu_id',
    'estado',
  ];

  protected $maps = [
      'id' => 'tia_id',
      'nombre' => 'tia_nombre',
      'descripcion' => 'tia_descripcion',
      'id_usuario' => 'usu_id',
  ];

  protected $appends = [
    'id',
    'nombre',
    'descripcion',
    'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function save(array $options = array())
  {
      if( ! $this->usu_id)
      {
          $this->usu_id = Auth::user()->id;
      }

      parent::save($options);
  }
}
