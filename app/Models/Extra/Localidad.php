<?php

namespace App\Models\Extra;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Localidad extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_localidades';
  protected $primaryKey = 'loc_id';

  protected $hidden = [
    'loc_id',
    'loc_nombre',
    'loc_codigo_postal',
    'pro_id',
  ];

  protected $maps = [
    'id' => 'pro_id',
    'nombre' => 'loc_nombre',
    'codigo_postal' => 'loc_codigo_postal',
    'id_provincia' => 'pro_id',
  ];

  protected $appends = [
    'id',
    'nombre',
    'codigo_postal',
    'id_provincia',
  ];
}