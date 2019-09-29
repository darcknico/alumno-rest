<?php

namespace App\Models\Extra;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Dia extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_dias';
  protected $primaryKey = 'dia_id';

  protected $hidden = [
    'dia_id',
    'dia_nombre',
  ];

  protected $maps = [
    'id' => 'dia_id',
    'nombre' => 'dia_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];
}