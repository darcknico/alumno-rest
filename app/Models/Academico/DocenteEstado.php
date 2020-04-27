<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class DocenteEstado extends Model
{
    use Eloquence, Mappable;

    protected $table = 'tbl_docente_estado';
    protected $primaryKey = 'des_id';

    protected $with = [
    	'tipo',
    ];

	protected $hidden = [
		'des_id',
		'usu_id',
		'tde_id',
		'des_fecha_inicial',
		'des_fecha_final',
		'des_observaciones',
		'des_archivo',
		'des_dir',
	];

	protected $maps = [
		'id' => 'des_id',
		'id_usuario' => 'usu_id',
		'id_tipo_docente_estado' => 'tde_id',
		'fecha_inicial' => 'des_fecha_inicial',
		'fecha_final' => 'des_fecha_final',
		'observaciones' => 'des_observaciones',
		'archivo' => 'des_archivo',
	];

	protected $appends = [
		'id',
		'id_usuario',
		'id_tipo_docente_estado',
		'fecha_inicial',
		'fecha_final',
		'observaciones',
		'archivo',
	];

	public function tipo(){
		return $this->hasOne('App\Models\Tipos\TipoDocenteEstado','tde_id','tde_id');
	}

	public function docente(){
		return $this->hasOne('App\Models\Academico\Docente','usu_id','usu_id');
	}
}
