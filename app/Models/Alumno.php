<?php

namespace App\Models;

use App\Models\AlumnoArchivo;
use App\Models\TipoAlumnoDocumentacion;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Redactors\LeftRedactor;

/**
 * @OA\Schema(
 *   schema="Alumno",
 *   type="object",
 *   required={"nombre","documento", "id_tipo_documento"},
 * )
 * Class Alumno
 * @package App\Models
 */
class Alumno extends Model implements Auditable
{

  use Eloquence,Mappable,\OwenIt\Auditing\Auditable;

  protected $table ='tbl_alumnos';
  protected $primaryKey = 'alu_id';

  protected $with = [
    'tipoDocumento',
    'tipo_civil',
    'tipo_estado',
  ];

  protected $casts = [
      'estado'=>'boolean',
      'fecha_nacimiento'=>'date',
  ];

  protected $hidden = [
    'alu_id',
    'sed_id',
    'alu_nombre',
    'alu_apellido',
    'alu_fecha_alta',
    'alu_codigo',
    'alu_domicilio',
    'alu_calle',
    'alu_numero',
    'alu_piso',
    'alu_depto',
    'loc_id',
    'alu_localidad',
    'pro_id',
    'alu_codigo_postal',
    'alu_telefono',
    'alu_celular',
    'alu_email',
    'tdo_id',
    'alu_documento',
    'alu_fecha_nacimiento',
    'alu_ciudad_nacimiento',
    'alu_nacionalidad',
    'alu_sexo',
    'tac_id',
    'tae_id',
    'alu_observaciones',
    'usu_id',
    'alu_password',
    'usu_id_baja',
  ];

  protected $maps = [
      'id' => 'alu_id',
      'id_sede' => 'sed_id',
      'nombre' => 'alu_nombre',
      'apellido' => 'alu_apellido',
      'fecha_alta' => 'alu_fecha_alta',
      'codigo' => 'alu_codigo',
      'domicilio' => 'alu_domicilio',
      'calle' => 'alu_calle',
      'numero' => 'alu_numero',
      'piso' => 'alu_piso',
      'depto' => 'alu_depto',
      'id_localidad' => 'loc_id',
      'localidad' => 'alu_localidad',
      'id_provincia' => 'pro_id',
      'codigo_postal' => 'alu_codigo_postal',
      'telefono' => 'alu_telefono',
      'celular' => 'alu_celular',
      'email' => 'alu_email',
      'id_tipo_documento' => 'tdo_id',
      'documento' => 'alu_documento',
      'fecha_nacimiento' => 'alu_fecha_nacimiento',
      'ciudad_nacimiento' => 'alu_ciudad_nacimiento',
      'nacionalidad' => 'alu_nacionalidad',
      'sexo' => 'alu_sexo',
      'id_tipo_alumno_civil' => 'tac_id',
      'id_tipo_alumno_estado' => 'tae_id',
      'observaciones' => 'alu_observaciones',
      'id_usuario' => 'usu_id',
      'id_usuario_baja' => 'usu_id_baja',
  ];

  /**
   * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="id_sede",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="nombre",type="string",maxLength=255)
   * @OA\Property(property="apellido",type="string",maxLength=255)
   * @OA\Property(property="domicilio",type="string",maxLength=255)
   * @OA\Property(property="calle",type="string",maxLength=255)
   * @OA\Property(property="numero",type="string",maxLength=255)
   * @OA\Property(property="piso",type="string",maxLength=255)
   * @OA\Property(property="depto",type="string",maxLength=255)
   * @OA\Property(property="id_localidad",type="integer", format="int64")
   * @OA\Property(property="localidad",type="string",maxLength=255)
   * @OA\Property(property="id_provincia",type="integer", format="int64")
   * @OA\Property(property="codigo_postal",type="integer", format="int64")
   * @OA\Property(property="telefono",type="string",maxLength=255)
   * @OA\Property(property="celular",type="string",maxLength=255)
   * @OA\Property(property="email",type="string",maxLength=255,format="email")
   * @OA\Property(property="id_tipo_documento",type="integer", format="int64")
   * @OA\Property(property="documento",type="integer", format="int64")
   * @OA\Property(property="fecha_nacimiento",type="string",format="date")
   * @OA\Property(property="ciudad_nacimiento",type="string",maxLength=255)
   * @OA\Property(property="nacionalidad",type="string",maxLength=255)
   * @OA\Property(property="sexo",type="string",maxLength=1)
   * @OA\Property(property="id_tipo_alumno_civil",type="integer", format="int64")
   * @OA\Property(property="id_tipo_alumno_estado",type="integer", format="int64")
   * @OA\Property(property="observaciones",type="string",maxLength=255)
   */
  protected $appends = [
      'id',
      'id_sede',
      'nombre',
      'apellido',
      'fecha_alta',
      'codigo',
      'domicilio',
      'calle',
      'numero',
      'piso',
      'depto',
      'id_localidad',
      'localidad',
      'id_provincia',
      'codigo_postal',
      'telefono',
      'celular',
      'email',
      'id_tipo_documento',
      'documento',
      'fecha_nacimiento',
      'ciudad_nacimiento',
      'nacionalidad',
      'sexo',
      'id_tipo_alumno_civil',
      'id_tipo_alumno_estado',
      'observaciones',
      'id_usuario',
      'id_usuario_baja',

      'archivos_subidos',
      'archivos_faltantes',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function usuario_baja(){
    return $this->hasOne('App\User','usu_id','usu_id_baja');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function tipoDocumento(){
    return $this->hasOne('App\Models\TipoDocumento','tdo_id','tdo_id');
  }

  public function tipo_civil(){
    return $this->hasOne('App\Models\TipoAlumnoCivil','tac_id','tac_id');
  }

  public function tipo_estado(){
    return $this->hasOne('App\Models\TipoAlumnoEstado','tae_id','tae_id');
  }

  public function provincia(){
    return $this->hasOne('App\Models\Extra\Provincia','pro_id','pro_id');
  }

  public function sedes(){
    return $this->hasMany('App\Models\Academico\AlumnoSede','alu_id','alu_id');
  }

  public function inscripciones(){
    return $this->hasMany('App\Models\Inscripcion','alu_id','alu_id');
  }

  public function asistencias(){
    return $this->hasMany('App\Models\Asistencia','alu_id','alu_id');
  }

  public function archivos(){
    return $this->hasMany('App\Models\AlumnoArchivo','alu_id','alu_id');
  }

  public function notificaciones(){
    return $this->hasMany('App\Models\AlumnoNotificacion','alu_id','alu_id');
  }

  protected $attributeModifiers = [
    'alu_password' => LeftRedactor::class,
  ];

  public function getArchivosSubidosAttribute(){
    $id_alumno = $this->id??0;
    $archivos = TipoAlumnoDocumentacion::whereHas('alumnos',function($q)use($id_alumno){
      $q->where('estado',1)->where('id_alumno',$id_alumno);
    })
    ->where('estado',1)
    ->get();
    return $archivos;
  }
  public function getArchivosFaltantesAttribute(){
    $id_alumno = $this->id??0;
    $archivos = TipoAlumnoDocumentacion::whereDoesntHave('alumnos',function($q)use($id_alumno){
      $q->where('estado',1)->where('id_alumno',$id_alumno);
    })
    ->where('estado',1)
    ->get();
    return $archivos;
  }
}
