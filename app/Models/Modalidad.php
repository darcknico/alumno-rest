<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

/**
* @OA\Schema(
*   schema="Modalidad",
*   type="object",
*   required={"nombre"},
* )
* Class Modalidad
* @package App\Models
*/
class Modalidad extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_modalidades';
  protected $primaryKey = 'mod_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'mod_id',
    'mod_nombre',
    'mod_descripcion',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'mod_id',
      'nombre' => 'mod_nombre',
      'descripcion' => 'mod_descripcion',
      'id_usuario' => 'usu_id',
  ];

   /**
   * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="id_usuario",type="integer", format="int64", readOnly=true, description="Usuario responsable del registro")
   * @OA\Property(property="nombre",type="string",maxLength=255)
   * @OA\Property(property="descripcion",type="string",maxLength=255)
   */
  protected $appends = [
      'id',
      'nombre',
      'descripcion',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }
}
