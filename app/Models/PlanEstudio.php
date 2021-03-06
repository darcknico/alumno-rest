<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

use App\Models\Materia;

/**
* @OA\Schema(
*   schema="PlanEstudio",
*   type="object",
*   required={"nombre","codigo","anio"},
* )
* Class PlanEstudio
* @package App\Models
*/
class PlanEstudio extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_planes_estudio';
  protected $primaryKey = 'pes_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'pes_id',
    'pes_nombre',
    'pes_codigo',
    'pes_anio',
    'pes_horas',
    'pes_resolucion',
    'car_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'pes_id',
      'nombre' => 'pes_nombre',
      'codigo' => 'pes_codigo',
      'anio' => 'pes_anio',
      'horas' => 'pes_horas',
      'resolucion' => 'pes_resolucion',
      'id_carrera' => 'car_id',
      'id_usuario' => 'usu_id',
  ];

  /**
  * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
  * @OA\Property(property="id_carrera",type="integer", format="int64", description="Carrera")
  * @OA\Property(property="id_usuario",type="integer", format="int64", readOnly=true, description="Usuario responsable del registro")
  * @OA\Property(property="nombre",type="string",maxLength=255)
  * @OA\Property(property="codigo",type="string",maxLength=255)
  * @OA\Property(property="anio",type="integer")
  * @OA\Property(property="horas",type="integer")
  * @OA\Property(property="cantidad_horas",type="integer", readOnly=true)
  */
  protected $appends = [
      'id',
      'nombre',
      'codigo',
      'anio',
      'horas',
      'resolucion',
      'id_carrera',
      'id_usuario',
      'cantidad_horas',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function carrera(){
    return $this->hasOne('App\Models\Carrera','car_id','car_id');
  }

  public function materias(){
    return $this->hasMany('App\Models\Materia','pes_id','pes_id');
  }

  public function getCantidadHorasAttribute(){
    $todo = Materia::selectRaw('sum(mat_horas) as total')->where([
      'pes_id' => $this['id'],
      'estado' => 1,
    ])
    ->groupBy('pes_id')->first();
    $horas = $todo['total']??0;
    if($horas>0){
      $horas = "2".$horas;
    }
    return $horas;
  }
}
