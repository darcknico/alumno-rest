<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class AlumnoDispositivo extends Model
{
	use Eloquence, Mappable, Notifiable;

    protected $table = 'tbl_alumno_dispositivo';
    protected $primaryKey = 'adi_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [

    ];

	protected $hidden = [
		'adi_id',
		'alu_id',
		'adi_device_id',
		'adi_device_os',
		'adi_device_model',
		'adi_manufacturer',
	];

	protected $maps = [
		'id' => 'adi_id',
		'id_alumno' => 'alu_id',
		'device_id' =>  'adi_device_id',
		'device_os' =>  'adi_device_os',
		'device_model' =>  'adi_device_model',
		'manufacturer' =>  'adi_manufacturer',
	];

	protected $appends = [
		'id',
		'id_alumno',
		'device_id',
		'device_os',
		'device_model',
		'manufacturer',
	];

	public function alumno(){
		return $this->hasOne('App\Models\Alumno','alu_id','alu_id');
	}

	public function routeNotificationForExpoPush()
    {
         return $this->device_id;
    }

}