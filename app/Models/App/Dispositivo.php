<?php

namespace App\Models\App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use App\User;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class Dispositivo extends Model
{
	use Eloquence, Mappable, Notifiable;

    protected $table = 'tbl_usuario_dispositivo';
    protected $primaryKey = 'udi_id';

    protected $casts = [
		'estado'=>'boolean',
	];

    protected $with = [

    ];

	protected $hidden = [
		'udi_id',
		'usu_id',
		'udi_device_id',
		'udi_device_os',
		'udi_device_model',
		'udi_manufacturer',
	];

	protected $maps = [
		'id' => 'udi_id',
		'id_usuario' => 'usu_id',
		'device_id' =>  'udi_device_id',
		'device_os' =>  'udi_device_os',
		'device_model' =>  'udi_device_model',
		'manufacturer' =>  'udi_manufacturer',
	];

	protected $appends = [
		'id',
		'id_usuario',
		'device_id',
		'device_os',
		'device_model',
		'manufacturer',
	];

	public function usuario(){
		return $this->hasOne('App\User','usu_id','usu_id');
	}

	public function routeNotificationForOneSignal()
    {
         return $this->device_id;
    }

}
