<?php

namespace App\Models\Tipos;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class TipoDocenteEstado extends Model
{
	use Eloquence, Mappable;

	protected $table ='tbl_tipo_docente_estado';
	protected $primaryKey = 'tde_id';

	protected $casts = [
		'estado'=>'boolean',
	];

	protected $hidden = [
		'tde_id',
		'tde_nombre',
		'tde_descripcion',
		'estado',
	];

	protected $maps = [
		'id' => 'tde_id',
		'nombre' => 'tde_nombre',
		'descripcion' => 'tde_descripcion',
	];

	protected $appends = [
		'id',
		'nombre',
		'descripcion',
	];
}
