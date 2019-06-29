<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class FormaPago extends Model
{
  use Eloquence, Mappable;

  protected $table ='tbl_forma_pago';
  protected $primaryKey = 'fpa_id';

  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
    'fpa_id',
    'fpa_nombre',
    'estado',
  ];

  protected $maps = [
      'id' => 'fpa_id',
      'nombre' => 'fpa_nombre',
  ];

  protected $appends = [
    'id',
    'nombre',
  ];

}
