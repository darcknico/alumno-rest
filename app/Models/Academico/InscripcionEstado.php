<?php

namespace App\Models\Academico;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class InscripcionEstado extends Model
{
	use Eloquence, Mappable;
    
    protected $table = 'tbl_inscripcion_estado';
    protected $primaryKey = 'ies_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [
    	'actual',
    	'anterior',
    	'usuario',
    ];

	protected $hidden = [
		'ies_id',
		'tie_id',
		'tie_id_tipo_inscripcion_estado',
		'ies_fecha',
		'ies_observaciones',
		'ins_id',
		'usu_id',
	];

	protected $maps = [
		'id' => 'ies_id',
		'id_tipo_inscripcion_estado' => 'tie_id',
		'anterior_id_tipo_inscripcion_estado' => 'tie_id_tipo_inscripcion_estado',
		'fecha' => 'ies_fecha',
		'observaciones' => 'ies_observaciones',
		'id_inscripcion' => 'ins_id',
		'id_usuario' => 'usu_id',
	];

	protected $appends = [
		'id',
		'id_tipo_inscripcion_estado',
		'anterior_id_tipo_inscripcion_estado',
		'fecha',
		'observaciones',
		'id_inscripcion',
		'id_usuario',
	];


	public function inscripcion(){
		return $this->hasOne('App\Models\Inscripcion','ins_id','ins_id');
	}
	
	public function actual(){
		return $this->hasOne('App\Models\TipoInscripcionEstado','tie_id','tie_id');
	}
	public function anterior(){
		return $this->hasOne('App\Models\TipoInscripcionEstado','tie_id','tie_id_tipo_inscripcion_estado');
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
