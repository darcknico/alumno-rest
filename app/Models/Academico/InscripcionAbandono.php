<?php

namespace App\Models\Academico;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class InscripcionAbandono extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_inscripcion_abandono';
    protected $primaryKey = 'iab_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [
    	'tipo',
    ];

	protected $hidden = [
		'iab_id',
		'tia_id',
		'ins_id',
		'usu_id',
	];

	protected $maps = [
		'id' => 'iab_id',
		'id_tipo_inscripcion_abandono' => 'tia_id',
		'id_inscripcion' => 'ins_id',
		'id_usuario' => 'usu_id',
	];

	protected $appends = [
		'id',
		'id_tipo_inscripcion_abandono',
		'id_inscripcion',
		'id_usuario',
	];

	public function inscripcion(){
		return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
	}

	public function tipo(){
		return $this->hasOne('App\Models\Tipos\TipoInscripcionAbandono','tia_id','tia_id');
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
