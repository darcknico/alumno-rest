<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreferenciaRequest;
use Illuminate\Http\Request;
use App\Models\MP\IPN;
use App\Models\MP\Webhook;
use App\Models\PaymentMercadoPago;
use App\Models\Obligacion;
use App\Views\Deuda;
use App\Events\PagoMercadoPagoModificado;
use App\Events\PagoMercadoPagoCreado;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreferenciaPagoCreado;
use App\PaymentMethods\MercadoPago;
use Validator;
use DB;

class MercadoPagoController extends Controller
{
	public function webhook(Request $request){
    	$id = $request->input('id');
    	$live_mode = $request->input('live_mode');
    	$type = $request->input('type');
    	$date_created = $request->input('date_created');
    	$application_id = $request->input('application_id');
    	$user_id = $request->input('user_id');
    	$version = $request->input('version');
    	$api_version = $request->input('api_version');
    	$action = $request->input('action');
    	$data = $request->input('data');

    	$todo = new Webhook;
    	$todo->mp_id = $id;
    	$todo->live_mode = $live_mode;
    	$todo->type = $type;
    	$todo->date_created = $date_created;
    	$todo->application_id = $application_id;
    	$todo->user_id = $user_id;
    	$todo->version = $version;
    	$todo->api_version = $api_version;
    	$todo->action = $action;
    	$todo->data_id = $data['id'];
    	$todo->save();

    	$response = $this->checkPaymentRequest($request);
    	return response()->json($todo,200);
    }

    public function ipn(Request $request){
    	$id = $request->input('id');
    	$topic = $request->input('topic');

    	$todo = new IPN;
    	$todo->mp_id = $id;
    	$todo->topic = $topic;
    	$todo->save();

    	$response = $this->checkPaymentRequest($request);
    	return response()->json($todo,200);
    }

    public function preferencia(Request $request){
        $validator = Validator::make($request->all(),[
            'id_obligacion' => 'required',
            'monto' => 'required',
            'email' => 'required | email',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $email = $request->input('email');
        $monto = $request->input('monto');
        $id_obligacion = $request->input('id_obligacion');
        $observaciones = $request->input('observaciones','');

        $obligacion = Obligacion::find($id_obligacion);
        if(!$obligacion->plan_pago->sede->mercadopago){
            return response()->json(['message'=>'La sede no tiene habilitado los pagos por mercadopago'],403);
        }

        $preferencia = [
            'id_inscripcion' => $obligacion->plan_pago->id_inscripcion,
            'id_obligacion' => $id_obligacion,
            'monto' => $monto,
            'email' => $email,
            'obligacion' => $obligacion,
            'observaciones' => $observaciones,
        ];
        $pago = new MercadoPago;
        $mercadopago = $pago->setupPaymentAndGetRedirectURL($preferencia);
        
        return response()->json($mercadopago);
    }

    public function verificar(Request $request){
        $validator = Validator::make($request->all(),[
            'id_obligacion' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $id_obligacion = $request->input('id_obligacion');

        $preferencia = PaymentMercadoPago::where('id_obligacion',$id_obligacion)->first();

        $pago = new MercadoPago;
        $mercadopago = $pago->checkPreferenceStatus($preferencia);
        
        return response()->json($mercadopago);
    }

	protected function generatePaymentGateway($orden)
	{
	    $method = new MercadoPago;

	    return $method->setupPaymentAndGetRedirectURL($orden);
	}

	protected function checkPaymentRequest($request)
	{
		$method = new MercadoPago;

	    return $method->checkPaymentRequest($request);
	}

	protected function checkPaymentStatus($payment_id)
	{
		$method = new MercadoPago;

	    return $method->checkPaymentStatus($payment_id);
	}

	protected function checkPreferenceStatus($pago)
	{
		$method = new MercadoPago;

	    return $method->checkPreferenceStatus($pago);
	}
}
