<?php

namespace App\PaymentMethods;

use App\Models\PaymentMercadoPago;
use App\Models\Sede;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;
use App\Models\Movimiento;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;
use App\Models\Beca;

use Illuminate\Http\Request;
use MercadoPago\Item;
use MercadoPago\MerchantOrder;
use MercadoPago\Payer;
use MercadoPago\Payment;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Functions\AuxiliarFunction;
use App\Functions\CuentaCorrienteFunction;
use App\Functions\DiariaFunction;
use App\Functions\PlanPagoFunction;
use App\Functions\ObligacionFunction;

use App\Mail\PreferenciaPagoCreado;
use App\Mail\PreferenciaPagoAprobado;
use DB;

use App\Http\Controllers\PlanPagoController;

class MercadoPago
{
  public function __construct()
  {
    SDK::setAccessToken(
      config("payment-methods.mercadopago.access_token")
    );
    SDK::setClientId(
      config("payment-methods.mercadopago.client")
    );
    SDK::setClientSecret(
      config("payment-methods.mercadopago.secret")
    );
    
  }

  /*
  id_obligacion //CUOTA
  id_inscripcion //inscripcion
  payment_id
  payment_status
  */
  public function setupPaymentAndGetRedirectURL($preferencia)
  {
    
    $preference = PaymentMercadoPago::where('id_obligacion',$preferencia['id_obligacion'])->where('estado',1)->first();
    if($preference){
      return $preference;
    }
    $pago = new PaymentMercadoPago;
    $pago->id_obligacion = $preferencia['id_obligacion'];
    $pago->id_inscripcion = $preferencia['id_inscripcion'];
    $pago->email = $preferencia['email'];
    $pago->monto = $preferencia['monto'];
    $pago->observaciones = $preferencia['observaciones'];
    $pago->save();

    $preference = new Preference();
    $items = [];
    $item = new Item();
    $item->id = $preferencia['id_obligacion'];
    $item->title = $preferencia['obligacion']['descripcion'];
    $item->quantity = 1;
    $item->currency_id = 'ARS';
    $item->unit_price = $preferencia['monto'];

    $items[] = $item;

    $payer = new Payer();
    $payer->email = $preferencia['email'];
    $preference->items = $items;
    $preference->payer = $payer;
    $preference->external_reference = $pago->id;

    $preference->back_urls = [
      "success" => route('mercadopago.success'),
      "pending" => route('mercadopago.pending'),
      "failure" => route('mercadopago.failure'),
    ];

    $preference->auto_return = "all";
    //$preference->notification_url = route('mercadopago.ipn');
    $preference->notification_url = "http://api.sistema.ariasdesaavedra.edu.ar/api/mercadopago/ipn";

    $preference->save();

    $url = $this->getPreferenceUrl($preference);
    $pago->preference_url = $url;
    $pago->preference_id = $preference->id;
    $pago->save();
    

    Mail::to($preferencia['email'])->send(new PreferenciaPagoCreado($pago));
    return $pago;
  }

  public function getPreferenceUrl($preference){
    if (AuxiliarFunction::is_true(config('payment-methods.use_sandbox'))) {
      $url = $preference->sandbox_init_point;
    } else {
      $url = $preference->init_point;
    }
    return $url;
  }

  /*
  OBTIENE EL METODO DE PAGO ELEGIDO Y EL ESTADO
  */
  public function checkPaymentRequest($request){
    /*
    WEEBHOOK
    */
    $mp_id = $request->input('id');
    $mp_live_mode = $request->input('live_mode');
    $mp_type = $request->input('type');
    $mp_date_created = $request->input('date_created');
    $mp_application_id = $request->input('application_id');
    $mp_user_id = $request->input('user_id');
    $mp_version = $request->input('version');
    $mp_api_version = $request->input('api_version');
    $mp_action = $request->input('action');
    $mp_data = $request->input('data');

    /*
    IPN
    */
    $ipn_id = $request->query('id');
    $ipn_resource = $request->input('resource');
    $ipn_topic = $request->input('topic');

    $pago = null;
    switch($mp_type) {
      case "payment":
        $response = Payment::find_by_id($mp_data['id']);
        if($response){
          $pago = PaymentMercadoPago::find($response->external_reference);
          $estado_previo = $pago->payment_status;
          $pago->payment_id = $response->id;
          $pago->payment_status = $response->status;
          $pago->save();
          if($pago and $estado_previo != 'approved' and $response->status == 'approved'){
            //NOTIFICAR PAGO REALIZADO
            $pago->fecha_pagado = Carbon::now();
            $pago->save();
            self::aprobarPago($pago);
            $to = [
            [
                'email' => $pago->email, 
                'name' => 'Pago aprobado',
            ]
            ];
            Mail::to($to)
                ->send(new PreferenciaPagoAprobado($pago));
          }
        }
        break;
    }
    switch ($ipn_topic) {
      case 'topic':
        $response = MerchantOrder::find_by_id($ipn_id);
        break;
    }
    return $pago;
  }

  /*
  ACTUALIZA EL ESTADO DEL PAGO
  */
  public function checkPaymentStatus($id){
    $response = Payment::find_by_id($id);
    $pago = null;
    if($response){
      $pago = PaymentMercadoPago::find($response->external_reference);
      $estado_previo = $pago->payment_status;
      $pago->payment_id = $response->id;
      $pago->payment_status = $response->status;
      $pago->save();

      if($pago and $estado_previo != 'approved' and $pago->payment_status == 'approved'){
          //NOTIFICAR PAGO REALIZADO
          $pago->fecha_pagado = Carbon::now();
          $pago->save();
          self::aprobarPago($pago);
          $to = [
          [
              'email' => $pago->email, 
              'name' => 'Pago aprobado',
          ]
          ];
          Mail::to($to)
              ->send(new PreferenciaPagoAprobado($pago));
      }
    }
    return $pago;
  }

  public function checkPreferenceStatus(PaymentMercadoPago $pago){
    $preference = Preference::find_by_id($pago->preference_id);
    if($preference){
      
    }
    return $pago;
  }

  public function deletePreference(PaymentMercadoPago $pago){
    $preference = Preference::find_by_id($pago->preference_id);
    if($preference){
      Log::info($preference->expires);
      $preference->expires = true;
      $preference->update();
    }
    $pago->obl_id = null;
    $pago->save();

    return $pago;
  }

  public function aprobarPago($payment){
    $monto = $payment->monto;
    $fecha = Carbon::parse($payment->fecha_pagado);
    $plan_pago = PlanPago::find($payment->obligacion->id_plan_pago);
    $id_plan_pago = $plan_pago->id;
    $id_sede = $plan_pago->id_sede;
    $sede = Sede::find($id_sede);

    $bonificar_intereses = false;
    $bonificar_cuotas = false;
    $especial_covid = false;
    $descripcion = "Mercado Pago: Pago ".$payment->obligacion->descripcion;

    $movimiento = new Movimiento;
    $movimiento->monto = $monto;
    $movimiento->fecha = $fecha;
    //$movimiento->cheque_numero = $cheque_numero;
    //$movimiento->cheque_banco = $cheque_banco;
    //$movimiento->cheque_origen = $cheque_origen;
    //$movimiento->cheque_vencimiento = empty($cheque_vencimiento)?null:Carbon::parse($cheque_vencimiento);
    $movimiento->descripcion = $descripcion;
    //$movimiento->numero_transaccion = $numero_transaccion;
    $movimiento->id_forma_pago = 6; // MERCADO PAGO
    $movimiento->id_tipo_movimiento = 1; // PAGO DE CUOTAS
    $movimiento->id_sede = $id_sede;
    $movimiento->id_usuario = 2; //ADMINISTRADOR
    $movimiento->id_tipo_egreso_ingreso = 1;
    $movimiento->save();

    $diaria = DiariaFunction::agregar($id_sede,$movimiento->id);

    $saldo = $monto;
    if($bonificar_intereses or $especial_covid){
      $detalles = PlanPagoController::detallePreparar($id_plan_pago,2,$fecha,$saldo,[
        'bonificar_cuotas' => $bonificar_cuotas,
        'especial_covid' => $especial_covid,
      ]);
    } else {
      $detalles = PlanPagoController::detallePreparar($id_plan_pago,1,$fecha,$saldo,[
        'bonificar_cuotas' => $bonificar_cuotas,
        'especial_covid' => $especial_covid,
      ]);
    }

    $obligacion = new Obligacion;
    $obligacion->monto = $monto;
    $obligacion->descripcion = $descripcion;
    $obligacion->saldo = 0;
    $obligacion->fecha = $fecha;
    $obligacion->fecha_vencimiento = $fecha;
    $obligacion->ppa_id = $id_plan_pago;
    $obligacion->tob_id = 3;
    $obligacion->id_usuario = 2; // ADMINISTRADOR
    $obligacion->save();

    $numero = $sede->pago_numero + 1;
    $pago = new Pago;
    $pago->fecha = $fecha;
    $pago->monto = $monto;
    $pago->descripcion = $descripcion;
    $pago->id_usuario = 2; // ADMINISTRADOR
    $pago->id_plan_pago = $id_plan_pago;
    $pago->id_obligacion = $obligacion->id;
    $pago->id_sede = $id_sede;
    $pago->id_movimiento = $movimiento->id;
    $pago->id_inscripcion = $payment->id_inscripcion;
    //$pago->numero_oficial = $numero_oficial;
    $pago->numero = $numero;
    $pago->save();
    $sede->pago_numero = $numero;
    $sede->save();

    foreach ($detalles as $detalle) {
      $parcial = new ObligacionPago;
      $parcial->opa_monto = $detalle['pagado'];
      $parcial->obl_id = $detalle['id_obligacion'];
      $parcial->pag_id = $pago->pag_id;
      $parcial->id_usuario = 2; // ADMINISTRADOR
      $parcial->save();

      $obligacion = Obligacion::where('obl_id',$detalle['id_obligacion'])->first();
      $obligacion = ObligacionFunction::actualizar($obligacion);
    }
    PlanPagoFunction::actualizar($plan_pago);
  }
}