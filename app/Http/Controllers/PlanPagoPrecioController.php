<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\PlanPagoPrecio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use App\Functions\CuentaCorrienteFunction;

class PlanPagoPrecioController extends Controller
{
  public function ultimo(Request $request){
    $id_sede = $request->route('id_sede');
    $todo = CuentaCorrienteFunction::ultimo_precio_plan($id_sede);
    return response()->json($todo,200);
  }

  public function store(Request $request){
    $user = Auth::user();
    $id_sede = $request->route('id_sede');
    $validator = Validator::make($request->all(),[
        'matricula_monto' => 'required',
        'cuota_monto' => 'required',
        'interes_monto' => 'required',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $matricula_monto = $request->input('matricula_monto');
    $cuota_monto = $request->input('cuota_monto');
    $interes_monto = $request->input('interes_monto');

    $precio = new PlanPagoPrecio;
    $precio->id_sede = $id_sede;
    $precio->matricula_monto = $matricula_monto;
    $precio->cuota_monto = $cuota_monto;
    $precio->interes_monto = $interes_monto;
    $precio->id_usuario = $user->id;
    $precio->save();

    return response()->json($precio,200);
  }

  public function destroy(Request $request){
    $user = Auth::user();
    $id_plan_pago_precio = $request->route('id_plan_pago_precio');
    $precio = PlanPagoPrecio::find($id_plan_pago_precio);
    $precio->estado = 0;
    $precio->deleted_at = Carbon::now();
    $precio->id_usuario_baja = $user->id;
    $precio->save();
    return response()->json($precio,200);
  }


}
