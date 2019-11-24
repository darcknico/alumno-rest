<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

/**
* @OA\Schema(
*   schema="Carrera",
*   type="object",
*   required={"nombre","nombre_corto","titulo"},
* )
* Class Carrera
* @package App\Models
*/
class Carrera extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_carreras';
  protected $primaryKey = 'car_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'car_id',
    'dep_id',
    'car_nombre',
    'car_nombre_corto',
    'car_descripcion',
    'car_titulo',
    'pes_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'car_id',
      'nombre' => 'car_nombre',
      'nombre_corto' => 'car_nombre_corto',
      'descripcion' => 'car_descripcion',
      'titulo' => 'car_titulo',
      'id_departamento' => 'dep_id',
      'id_plan_estudio' => 'pes_id',
      'id_usuario' => 'usu_id',
  ];

  /**
  * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
  * @OA\Property(property="id_departamento",type="integer", format="int64", description="Departamento que pertenece la carrera")
  * @OA\Property(property="id_usuario",type="integer", format="int64", readOnly=true, description="Usuario responsable del registro")
  * @OA\Property(property="nombre",type="string",maxLength=255)
  * @OA\Property(property="nombre_corto",type="string",maxLength=255)
  * @OA\Property(property="descripcion",type="string",maxLength=255)
  * @OA\Property(property="titulo",type="string",maxLength=255)
  */
  protected $appends = [
      'id',
      'nombre',
      'nombre_corto',
      'descripcion',
      'titulo',
      'id_departamento',
      'id_plan_estudio',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function departamento(){
    return $this->hasOne('App\Models\Departamento','dep_id','dep_id');
  }

  public function plan_estudio(){
    return $this->hasOne('App\Models\PlanEstudio','pes_id','pes_id');
  }

  public function modalidades(){
    return $this->hasMany('App\Models\CarreraModalidad','car_id','car_id');
  }

  public function planesEstudio(){
    return $this->hasMany('App\Models\PlanEstudio','car_id','car_id');
  }
}
