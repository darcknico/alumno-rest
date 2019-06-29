<?php

namespace App\Http\Controllers;

use App\Models\Sede;
use App\Models\Pago;
use App\Models\Obligacion;
use App\Models\Movimiento;
use App\Models\TipoTramite;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class TramiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json([],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $sede = Sede::find($id_sede);

        $validator = Validator::make($request->all(),[
          'id_inscripcion' => 'required | integer',
          'id_tipo_tramite' => 'required | integer',
          'id_movimiento' => 'required | integer',
          'monto' => 'required | numeric',
          'fecha' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_inscripcion = $request->input('id_inscripcion');
        $id_tipo_tramite = $request->input('id_tipo_tramite');
        $id_movimiento = $request->input('id_movimiento');
        $monto = $request->input('monto');
        $fecha = Carbon::parse($request->input('fecha'));
        $descripcion = $request->input('descripcion','');
        $numero_oficial = $request->input('numero_oficial');

        $tipo = TipoTramite::find($id_tipo_tramite);
        $movimiento = Pago::where('id_movimiento',$id_movimiento)->first();
        if($movimiento){
            return response()->json(['error'=>'El movimiento se encuentra en uso'],403);
        }

        $obligacion = new Obligacion;
        $obligacion->monto = $monto;
        $obligacion->descripcion = 'Pago: '.$tipo->nombre;
        $obligacion->saldo = 0;
        $obligacion->fecha = $fecha->toDateString();
        $obligacion->fecha_vencimiento = $fecha->toDateString();
        $obligacion->id_tipo_obligacion = 20;
        $obligacion->id_usuario = $user->id;
        $obligacion->save();

        $numero = $sede->pago_numero + 1;
        $pago = new Pago;
        $pago->id_tipo_pago = 20;
        $pago->fecha = $fecha->toDateString();
        $pago->monto = $monto;
        $pago->descripcion = $descripcion;
        $pago->id_usuario = $user->id;
        $pago->obl_id = $obligacion->obl_id;
        $pago->id_sede = $id_sede;
        $pago->id_movimiento = $id_movimiento;
        $pago->id_inscripcion = $id_inscripcion;
        $pago->numero_oficial = $numero_oficial;
        $pago->numero = $numero;
        $pago->save();
        $sede->pago_numero = $numero;
        $sede->save();

        return response()->json($pago,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json([],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json([],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json([],200);
    }

    public function tipos(){
        $todo = TipoTramite::where('estado',1)->get();
        return response()->json($todo,200);
    }
}
