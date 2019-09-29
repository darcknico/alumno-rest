<?php

namespace App\Models\Academico;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class DocenteContrato extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_docente_contrato';
    protected $primaryKey = 'dco_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [
    	'tipo',
    ];

	protected $hidden = [
		'dco_id',
		'usu_id',
		'tco_id',
	];

	protected $maps = [
		'id' => 'ase_id',
		'id_usuario' => 'usu_id',
		'id_tipo_contrato' => 'tco_id',
	];

	protected $appends = [
		'id',
		'id_usuario',
		'id_tipo_contrato',
	];

	public function tipo(){
		return $this->hasOne('App\Models\Tipos\TipoContrato','tco_id','tco_id');
	}

}
