<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

/**
* @OA\Schema(
*   schema="Beca",
*   type="object",
*   required={"nombre","porcentaje"},
* )
* Class Beca
* @package App\Models
*/
class Beca extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_becas';
  protected $primaryKey = 'bec_id';

  protected $casts = [
      'estado'=>'boolean',
      'bec_porcentaje'=>'float',
      'bec_porcentaje_matricula'=>'float',
  ];

  protected $hidden = [
    'bec_id',
    'bec_nombre',
    'bec_descripcion',
    'bec_porcentaje',
    'bec_porcentaje_matricula',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'bec_id',
      'nombre' => 'bec_nombre',
      'descripcion' => 'bec_descripcion',
      'porcentaje' => 'bec_porcentaje',
      'porcentaje_matricula' => 'bec_porcentaje_matricula',
      'id_usuario' => 'usu_id',
  ];

  /**
  * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
  * @OA\Property(property="id_usuario",type="integer", format="int64", readOnly=true, description="Usuario responsable del registro")
  * @OA\Property(property="nombre",type="string",maxLength=255)
  * @OA\Property(property="descripcion",type="string",maxLength=255)
  * @OA\Property(property="porcentaje",type="number", format="double")
  */
  protected $appends = [
      'id',
      'nombre',
      'descripcion',
      'porcentaje',
      'porcentaje_matricula',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

}
