<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\Movimiento;
use App\Models\Diaria;
use App\Models\FormaPago;
use App\Models\TipoComprobante;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use App\Functions\DiariaFunction;
use Carbon\Carbon;

use App\Exports\MovimientoExport;

class MovimientoController extends Controller{

	public function index(Request $request){
		$id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $page = $request->query('page',0);
        $length = $request->query('length',0);
        $registros = Movimiento::with('forma','usuario')
        ->where([
        	'estado' => 1,
        	'sed_id' => $id_sede
        ]);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $page==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }

        $id_forma_pago = $request->query('id_forma_pago',0);
        $id_tipo_movimiento = $request->query('id_tipo_movimiento',0);
        $id_tipo_comprobante = $request->query('id_tipo_comprobante',0);
        $id_tipo_egreso_ingreso = $request->query('id_tipo_egreso_ingreso',-1);
        $fecha_inicio = $request->query('fecha_inicio',null);
        $fecha_fin = $request->query('fecha_fin',null);

        $registros = $registros
        	->when($id_forma_pago>0,function($q)use($id_forma_pago){
        		return $q->where('fpa_id',$id_forma_pago);
        	})
            ->when($id_tipo_movimiento>0,function($q)use($id_tipo_movimiento){
                return $q->where('id_tipo_movimiento',$id_tipo_movimiento);
            })
            ->when($id_tipo_comprobante>0,function($q)use($id_tipo_comprobante){
                return $q->where('id_tipo_comprobante',$id_tipo_comprobante);
            })
        	->when($id_tipo_egreso_ingreso>=0,function($q)use($id_tipo_egreso_ingreso){
        		return $q->where('tei_id',$id_tipo_egreso_ingreso);
        	})
            ->when(!empty($fecha_inicio),function($q)use($fecha_inicio){
                    return $q->whereDate('fecha','>=',$fecha_inicio);
                })
            ->when(!empty($fecha_fin),function($q)use($fecha_fin){
                return $q->whereDate('fecha','<=',$fecha_fin);
            });

    	$values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->whereRaw("DATE_FORMAT(mov_fecha, '%d/%m/%Y') like '%".$value."%'");
                });
              }
            }
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('created_at','desc');
        }
        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $estadisticas = $q->selectRaw('
        	count(*) as total_cantidad,
        	sum(if(fpa_id=1,mov_monto,0)) as total_efectivo,
        	sum(if(fpa_id=2,mov_monto,0)) as total_cheque,
        	sum(if(fpa_id=3,mov_monto,0)) as total_tarjeta,
        	sum(if(fpa_id=4,mov_monto,0)) as total_otros
        	')->groupBy('sed_id')->first();
        if($length>0){
        $registros = $registros->limit($length);
        if($page>1){
            $registros = $registros->offset(($page-1)*$length)->get();
        } else {
            $registros = $registros->get();
        }

        } else {
            $registros = $registros->get();
        }

        return response()->json([
            'total_count'=>intval($estadisticas->total_cantidad??0),
            'items'=>$registros,
            'total_efectivo'=>$estadisticas->total_efectivo??0,
            'total_cheque'=>$estadisticas->total_cheque??0,
            'total_tarjeta'=>$estadisticas->total_tarjeta??0,
            'total_otros'=>$estadisticas->total_otros??0,
        ],200);
	}

	public function ingreso(Request $request){
		$id_sede = $request->route('id_sede');
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'monto' => 'required',
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $monto = $request->input('monto');
        $fecha = $request->input('fecha');
        $cheque_numero = $request->input('cheque_numero');
        $cheque_banco = $request->input('cheque_banco');
        $cheque_origen = $request->input('cheque_origen');
        $cheque_vencimiento = $request->input('cheque_vencimiento');
        $descripcion = $request->input('descripcion');
        $id_forma_pago = $request->input('id_forma_pago',1);
        $id_tipo_movimiento = $request->input('id_tipo_movimiento');

        $todo = new Movimiento;
        $todo->monto = $monto;
        $todo->fecha = Carbon::parse($fecha);
        $todo->cheque_numero = $cheque_numero;
        $todo->cheque_banco = $cheque_banco;
        $todo->cheque_origen = $cheque_origen;
        $todo->cheque_vencimiento = empty($cheque_vencimiento)?null:Carbon::parse($cheque_vencimiento);
        $todo->descripcion = $descripcion;
        $todo->id_forma_pago = $id_forma_pago;
        $todo->id_tipo_movimiento = $id_tipo_movimiento;
        $todo->id_sede = $id_sede;
        $todo->id_usuario = $user->id;
        $todo->id_tipo_egreso_ingreso = 1;
        $todo->save();

        $diaria = DiariaFunction::agregar($id_sede,$todo->id);
        $todo['diaria'] = $diaria;
        return response()->json($todo,200);
	}

	public function egreso(Request $request){
		$id_sede = $request->route('id_sede');
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'monto' => 'required',
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $monto = $request->input('monto');
        $fecha = $request->input('fecha');
        $cheque_numero = $request->input('cheque_numero');
        $cheque_banco = $request->input('cheque_banco');
        $cheque_origen = $request->input('cheque_origen');
        $cheque_vencimiento = $request->input('cheque_vencimiento');
        $descripcion = $request->input('descripcion');
        $id_forma_pago = $request->input('id_forma_pago');
        $id_tipo_movimiento = $request->input('id_tipo_movimiento');
        $numero = $request->input('numero');
        $id_tipo_comprobante = $request->input('id_tipo_comprobante');

        $todo = new Movimiento;
        $todo->monto = $monto;
        $todo->fecha = Carbon::parse($fecha);
        $todo->cheque_numero = $cheque_numero;
        $todo->cheque_banco = $cheque_banco;
        $todo->cheque_origen = $cheque_origen;
        $todo->cheque_vencimiento = empty($cheque_vencimiento)?null:Carbon::parse($cheque_vencimiento);
        $todo->descripcion = $descripcion;
        $todo->id_forma_pago = $id_forma_pago;
        $todo->id_tipo_movimiento = $id_tipo_movimiento;
        $todo->numero = $numero;
        $todo->id_tipo_comprobante = $id_tipo_comprobante;
        $todo->id_sede = $id_sede;
        $todo->id_usuario = $user->id;
        $todo->id_tipo_egreso_ingreso = 0;
        $todo->save();

    	$diaria = DiariaFunction::agregar($id_sede,$todo->id);
        $todo['diaria'] = $diaria;
        return response()->json($todo,200);
	}

	public function show(Request $request){
		$id_movimiento = $request->route('id_movimiento');

		$todo = Movimiento::with('usuario','forma')->find($id_movimiento);

		return response()->json($todo,200);
	}

    public function update(Request $request){
        $id_sede = $request->route('id_sede');
        $id_movimiento = $request->route('id_movimiento');
        $user = Auth::user();

        $validator = Validator::make($request->all(),[
            'id_forma_pago' => 'required | integer',
            'id_tipo_movimiento' => 'required | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }

        $cheque_numero = $request->input('cheque_numero');
        $cheque_banco = $request->input('cheque_banco');
        $cheque_origen = $request->input('cheque_origen');
        $cheque_vencimiento = $request->input('cheque_vencimiento');
        $descripcion = $request->input('descripcion');
        $id_forma_pago = $request->input('id_forma_pago');
        $id_tipo_movimiento = $request->input('id_tipo_movimiento');
        $numero = $request->input('numero');
        $id_tipo_comprobante = $request->input('id_tipo_comprobante');

        $todo = Movimiento::find($id_movimiento);
        $todo->cheque_numero = $cheque_numero;
        $todo->cheque_banco = $cheque_banco;
        $todo->cheque_origen = $cheque_origen;
        $todo->cheque_vencimiento = empty($cheque_vencimiento)?null:Carbon::parse($cheque_vencimiento);
        $todo->descripcion = $descripcion;
        $todo->id_forma_pago = $id_forma_pago;
        $todo->id_tipo_movimiento = $id_tipo_movimiento;
        $todo->numero = $numero;
        $todo->id_tipo_comprobante = $id_tipo_comprobante;
        $todo->save();

        return response()->json($todo,200);
    }

	public function destroy(Request $request){
        $user = Auth::user();
		$id_sede = $request->route('id_sede');
		$id_movimiento = $request->route('id_movimiento');

        $movimiento = Movimiento::find($id_movimiento);
		$movimiento->estado = 0;
        $movimiento->deleted_at = Carbon::now();
        $movimiento->usu_id_baja = $user->id;
		$movimiento->save();

		DiariaFunction::quitar($id_sede,$id_movimiento);

		return response()->json($movimiento,200);
	}

	public function formas(Request $request){
		$todo = FormaPago::where('estado',1)->get();
        return response()->json($todo,200);
	}

    public function tipos_comprobante(Request $request){
        $todo = TipoComprobante::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function exportar(Request $request){
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $id_forma_pago = $request->query('id_forma_pago',0);
        $id_tipo_movimiento = $request->query('id_tipo_movimiento',0);
        $id_tipo_comprobante = $request->query('id_tipo_comprobante',0);
        $id_tipo_egreso_ingreso = $request->query('id_tipo_egreso_ingreso',-1);
        $fecha_inicio = $request->query('fecha_inicio',null);
        $fecha_fin = $request->query('fecha_fin',null);

        return (new MovimientoExport(
            $id_sede,$search,
            $id_forma_pago,
            $id_tipo_movimiento,
            $id_tipo_comprobante,
            $id_tipo_egreso_ingreso,$fecha_inicio,$fecha_fin))->download('pagos.xlsx');
    }

}