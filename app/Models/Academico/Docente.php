<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Docente extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_docentes';
    protected $primaryKey = 'usu_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $with = [
    	'usuario.sedes',
    	'contratos',
    ];

	protected $hidden = [
		'usu_id',
		'doc_titulo',
		'tco_id',
		'doc_cuit',
		'doc_observaciones',
	];

	protected $maps = [
		'id_usuario' => 'usu_id',
		'titulo' => 'doc_titulo',
		'id_tipo_contrato' => 'tco_id',
		'cuit' => 'doc_cuit',
		'observaciones' => 'doc_observaciones',
	];

	protected $appends = [
		'id_usuario',
		'titulo',
		'id_tipo_contrato',
		'cuit',
		'observaciones',
	];

	public function usuario(){
		return $this->hasOne('App\User','usu_id','usu_id');
	}

	public function contratos(){
		return $this->hasMany('App\Models\Academico\DocenteContrato','usu_id','usu_id')->where('estado',1);
	}

	public function carreras(){
		return $this->hasOne('App\Models\Academico\DocenteMateria','usu_id','usu_id');
	}
}
