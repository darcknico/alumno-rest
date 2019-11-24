<?php

namespace App\Models\App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Asistencia extends Model
{
	use Eloquence, Mappable;

    protected $table = 'tbl_usuario_asistencia';
    protected $primaryKey = 'uas_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [

    ];

	protected $hidden = [
		'uas_id',
		'usu_id',
		'sed_id',
		'uas_fecha',
		'uas_latitud',
		'uas_longitud',
		'udi_id',
	];

	protected $maps = [
		'id' => 'uas_id',
		'id_usuario' => 'usu_id',
		'id_sede' => 'sed_id',
		'fecha' => 'uas_fecha',
		'latitud' => 'uas_latitud',
		'longitud' => 'uas_longitud',
		'id_usuario_dispositivo' => 'udi_id',
	];

	protected $appends = [
		'id',
		'id_usuario',
		'id_sede',
		'fecha',
		'latitud',
		'longitud',
		'id_usuario_dispositivo',
	];

	public function usuario(){
		return $this->hasOne('App\User','usu_id','usu_id');
	}

	public function sede(){
		return $this->hasOne('App\Models\Sede','sed_id','sed_id');
	}

	public function dispositivo(){
		return $this->hasOne('App\Models\App\Dispositivo','uas_id','uas_id');
	}

}
