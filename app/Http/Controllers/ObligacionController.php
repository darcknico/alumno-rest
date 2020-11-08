<?php

namespace App\Http\Controllers;

use App\Models\PaymentMercadoPago;
use App\Models\Obligacion;
use Illuminate\Http\Request;
use Validator;
use App\PaymentMethods\MercadoPago;



class ObligacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json([]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $obligacion = Obligacion::with([
            'pagos.pago',
            'intereses',
            'interes',
            'obligacion',
            'tipo',
            'inscripcion.alumno',
        ])->find($request->obligacion);
        return response()->json($obligacion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $descripcion = $request->input('descripcion');
        $obligacion = Obligacion::find($request->obligacion);
        //$obligacion->descripcion = $descripcion;
        return response()->json($obligacion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return response()->json([]);
    }

    public function mercadopago(Request $request){
        $id_obligacion = $request->route('id_obligacion');
        $validator = Validator::make($request->all(),[
            'monto' => 'required',
            'email' => 'required | email',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $monto = $request->input('monto');
        $email = $request->input('email');
        $observaciones = $request->input('observaciones');

        $obligacion = Obligacion::find($id_obligacion);

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

    public function mercadopagoEliminar(Request $request){
        $id_obligacion = $request->route('id_obligacion');

        $preferencia = PaymentMercadoPago::where('id_obligacion',$id_obligacion)->where('estado',1)->first();

        if($preferencia){
            $pago = new MercadoPago;
            $preferencia = $pago->deletePreference($preferencia);
        }
        
        return response()->json($preferencia);
    }
}
