<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoDocumento extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_tipo_documentos';
  protected $primaryKey = 'tdo_id';

  protected $hidden = [
    'tdo_id',
    'tdo_nombre',
    'estado',
  ];

  protected $maps = [
    'id' => 'tdo_id',
    'nombre' => 'tdo_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];
}
