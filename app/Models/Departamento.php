<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

/**
* @OA\Schema(
*   schema="Departamento",
*   type="object",
*   required={"nombre"},
* )
* Class Departamento
* @package App\Models
*/
class Departamento extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_departamentos';
  protected $primaryKey = 'dep_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'dep_id',
    'dep_nombre',
    'sed_id',
    'usu_id',
  ];

  protected $maps = [
      'id' => 'dep_id',
      'nombre' => 'dep_nombre',
      'id_sede' => 'sed_id',
      'id_usuario' => 'usu_id',
  ];

  /**
   * @OA\Property(property="id",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="id_sede",type="integer", format="int64", readOnly=true)
   * @OA\Property(property="id_usuario",type="integer", format="int64", readOnly=true, description="Usuario responsable del registro")
   * @OA\Property(property="nombre",type="string",maxLength=255)
   */

  protected $appends = [
      'id',
      'nombre',
      'id_sede',
      'id_usuario',
  ];

  public function usuario(){
    return $this->hasOne('App\User','usu_id','usu_id');
  }

  public function sede(){
    return $this->hasOne('App\Models\Sede','sed_id','sed_id');
  }

  public function carreras(){
    return $this->hasMany('App\Models\Carrera','dep_id','dep_id');
  }

}
