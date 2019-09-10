<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class DocenteMateria extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_docente_materia';
    protected $primaryKey = 'dma_id';

    protected $with = [
    	'materia.planEstudio',
    	'carrera',
    ];

	protected $hidden = [
		'dma_id',
		'sed_id',
		'usu_id',
		'mat_id',
		'car_id',
	];

	protected $maps = [
		'id' => 'dma_id',
		'id_sede' => 'sed_id',
		'id_usuario' => 'usu_id',
		'id_materia' => 'mat_id',
		'id_carrera' => 'car_id',
	];

	protected $appends = [
		'id',
		'id_sede',
		'id_usuario',
		'id_materia',
		'id_carrera',
	];

	public function sede(){
		return $this->hasOne('App\Models\Sede','sed_id','sed_id');
	}

	public function docente(){
		return $this->hasOne('App\Models\Academico\Docente','usu_id','usu_id');
	}

	public function usuario(){
		return $this->hasOne('App\User','usu_id','usu_id');
	}

	public function materia(){
		return $this->hasOne('App\Models\Materia','mat_id','mat_id');
	}

	public function carrera(){
		return $this->hasOne('App\Models\Carrera','car_id','car_id');
	}
}
