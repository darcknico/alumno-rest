<?php

namespace App\Models\Extra;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Provincia extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_provincias';
  protected $primaryKey = 'pro_id';

  protected $hidden = [
    'pro_id',
    'pro_nombre',
  ];

  protected $maps = [
    'nombre' => 'pro_nombre',
    'id' => 'pro_id',
  ];

  protected $appends = [
    'nombre',
    'id',
  ];
}