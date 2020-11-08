<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class PaymentMercadoPago extends Model
{
	use Eloquence, Mappable;

    protected $table ='mp_payments';
	
	protected $with = [

	];

    protected $hidden = [
		'obl_id',
		'ins_id',
	];

	protected $maps = [
		'id_obligacion' => 'obl_id',
		'id_inscripcion' => 'ins_id',
	];

	protected $appends = [
		'id_obligacion',
		'id_inscripcion',
	];

	public function obligacion(){
		return $this->hasOne('App\Models\Obligacion','obl_id','obl_id');
	}
}
