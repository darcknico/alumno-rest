<?php

namespace App\Models\Tipos;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoMesaDocente extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_mesa_docente';
  protected $primaryKey = 'tmd_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'tmd_id',
    'tmd_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'tmd_id',
      'nombre' => 'tmd_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
