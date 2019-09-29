<?php

namespace App\Models\Academico;

use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Aula extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_aulas';
    protected $primaryKey = 'aul_id';

	protected $hidden = [
		'aul_id',
		'sed_id',
		'aul_numero',
		'aul_nombre',
		'aul_capacidad',
	];

	protected $maps = [
		'id' => 'aul_id',
		'id_sede' => 'sed_id',
		'numero' => 'aul_numero',
		'nombre' => 'aul_nombre',
		'capacidad' => 'aul_capacidad',
	];

	protected $appends = [
		'id',
		'id_sede',
		'numero',
		'nombre',
		'capacidad',
	];

	public function sede(){
		return $this->hasOne('App\Models\Sede','sed_id','sed_id');
	}
}
