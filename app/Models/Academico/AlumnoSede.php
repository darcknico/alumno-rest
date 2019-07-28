<?php

namespace App\Models\Academico;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AlumnoSede extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_alumno_sede';
    protected $primaryKey = 'ase_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [

    ];

	protected $hidden = [
		'ase_id',
		'alu_id',
		'sed_id',
		'usu_id',
	];

	protected $maps = [
		'id' => 'ase_id',
		'id_alumno' => 'alu_id',
		'id_sede' => 'sed_id',
		'id_usuario' => 'usu_id',
	];

	protected $appends = [
		'id',
		'id_alumno',
		'id_sede',
		'id_usuario',
	];

	public function alumno(){
		return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
	}

	public function sede(){
		return $this->hasOne('App\Models\Sede','sed_id','sed_id');
	}

	public function usuario(){
		return $this->hasOne('App\User','usu_id','usu_id');
	}

	public function save(array $options = array())
    {
        if( ! $this->usu_id)
        {
            $this->usu_id = Auth::user()->id;
        }

        parent::save($options);
    }

}
