<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

use App\Models\Plantilla;
use App\Models\Alumno;
use App\Models\AlumnoNotificacion;

class Notificacion extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_notificaciones';
  protected $primaryKey = 'not_id';

  protected $casts = [
      'estado'=>'boolean',
      'not_enviado'=>'boolean',
      'puede_email'=>'boolean',
      'puede_push'=>'boolean',
  ];

  protected $hidden = [
    'not_id',
    'not_nombre',
    'not_descripcion',
    'not_fecha',
    'not_enviado',
    'not_asunto',
    'not_responder_email',
    'not_responder_nombre',
    'not_puede_email',
    'not_puede_push',
    'pla_id',
    'sed_id',
    'usu_id', 
  ];

  protected $maps = [
    'id' => 'not_id',
    'nombre' => 'not_nombre',
    'descripcion' => 'not_descripcion',
    'fecha' => 'not_fecha',
    'enviado' => 'not_enviado',
    'asunto' => 'not_asunto',
    'responder_email' => 'not_responder_email',
    'responder_nombre' => 'not_responder_nombre',
    'puede_email' => 'not_puede_email',
    'puede_push' => 'not_puede_push',
    'id_plantilla' => 'pla_id',
    'id_sede' => 'sed_id',
    'id_usuario' => 'usu_id',
  ];

  protected $appends = [
    'id',
    'nombre',
    'descripcion',
    'fecha',
    'enviado',
    'asunto',
    'responder_email',
    'responder_nombre',
    'puede_email',
    'puede_push',
    'id_plantilla',
    'id_sede',
    'id_usuario',

    'plantilla',

    //'alumnos_asociados',
    'correos_enviado',
    'correos_abierto',
    'total',
  ];

  public function getPlantillaAttribute(){
  	if(!is_null($this['pla_id'])){
  		return Plantilla::with('usuario')->select('pla_id','pla_titulo','pla_descripcion','created_at','updated_at','usu_id')->where([
    		'pla_id'=>$this['pla_id'],
    	])->first();
  	}
    return null;
  }

  public function alumnos(){
    return $this->hasMany('App\Models\AlumnoNotificacion','not_id','not_id');
  }

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
/*
  public function getAlumnosAsociadosAttribute(){
    $Alumnos = AlumnoNotificacion::where([
      'not_id' => $this['id'],
      'estado' => 1,
    ])->pluck('alu_id')->toArray();
    return Alumno::with('tipoDocumento')->whereIn('alu_id',$Alumnos)->get();
  }
*/

  public function getCorreosEnviadoAttribute(){
    $alumnos = AlumnoNotificacion::where([
      'not_id' => $this['id'],
      'estado' => 1,
      'ano_enviado' => 1,
    ])->pluck('alu_id')->toArray();
    return count($alumnos);
  }

  public function getCorreosAbiertoAttribute(){
    $alumnos = AlumnoNotificacion::where([
      'not_id' => $this['id'],
      'estado' => 1,
      'ano_enviado' => 1,
    ])->whereNotNull('ano_visto')->pluck('alu_id')->toArray();
    return count($alumnos);
  }

  public function getTotalAttribute(){
    $alumnos = AlumnoNotificacion::where([
      'not_id' => $this['id'],
      'estado' => 1,
    ])->pluck('alu_id')->toArray();
    return count($alumnos);
  }

}
