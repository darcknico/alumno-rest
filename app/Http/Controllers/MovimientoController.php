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
use \DB;

class MovimientoController extends Controller{

	public function index(Request $request){
		$id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $page = $request->query('page',0);
        $length = $request->query('length',0);
        $registros = Movimiento::with('forma','usuario','pago')
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
                  $query
                    ->where('descripcion','like','%'.$value.'%')
                    ->orWhereHas('pago',function($q)use($value){
                        $q->where('numero_oficial','like',$value.'%');
                    });
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
        $numero_transaccion = $request->input('numero_transaccion');

        $todo = new Movimiento;
        $todo->monto = $monto;
        $todo->fecha = Carbon::parse($fecha);
        $todo->cheque_numero = $cheque_numero;
        $todo->cheque_banco = $cheque_banco;
        $todo->cheque_origen = $cheque_origen;
        $todo->cheque_vencimiento = empty($cheque_vencimiento)?null:Carbon::parse($cheque_vencimiento);
        $todo->descripcion = $descripcion;
        $todo->numero_transaccion = $numero_transaccion;
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
        $numero_transaccion = $request->input('numero_transaccion');

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
        $todo->numero_transaccion = $numero_transaccion;
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

    public function estadisticas_diaria(Request $request){
        $id_sede = $request->route('id_sede');
        $id_tipo_egreso_ingreso = $request->query('id_tipo_egreso_ingreso',1);
        $fecha_inicio = $request->query('fecha_inicio',null);
        $fecha_fin = $request->query('fecha_fin',null);
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required | date',
            'fecha_fin' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $fecha_inicio = Carbon::parse($fecha_inicio);
        $fecha_fin = Carbon::parse($fecha_fin);
        $dias = $fecha_inicio->diffInDays($fecha_fin);
        $sequence = 'seq_0_to_'.$dias;

        $sql = "
            SELECT d.date as fecha,
            COALESCE(sum( IF(mov.tei_id=1,mov.mov_monto,0) ), 0) as total_ingresos,
            COALESCE(sum( IF(mov.tei_id=0,mov.mov_monto,0) ), 0) as total_egresos,
            COALESCE(sum( IF(mov.tei_id=1,1,0) ), 0) as cantidad_ingresos,
            COALESCE(sum( IF(mov.tei_id=0,1,0) ), 0) as cantidad_egresos
            FROM ( SELECT ? + INTERVAL seq DAY AS date 
                FROM ".$sequence." AS offs
                ) d LEFT OUTER JOIN
                tbl_movimientos mov 
                ON d.date = mov.mov_fecha and mov.sed_id = ? and mov.estado = 1
            GROUP BY d.date
            order by fecha
                ";
        $results = DB::select($sql, [
            $fecha_inicio->toDateString(),
            $id_sede,
            ]
            );
        return response()->json($results,200);
    }

    public function estadisticas_tipo(Request $request){
        $id_sede = $request->route('id_sede');
        $length = $request->query('length',5);
        $id_tipo_egreso_ingreso = $request->query('id_tipo_egreso_ingreso',1);
        $fecha_inicio = $request->query('fecha_inicio',null);
        $fecha_fin = $request->query('fecha_fin',null);
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required | date',
            'fecha_fin' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $fecha_inicio = Carbon::parse($fecha_inicio);
        $fecha_fin = Carbon::parse($fecha_fin);
        if($length>0){
            $results = DB::select("
                    SELECT sum(mov.mov_monto) as total,count(mov.mov_monto) as cantidad , tmo.tmo_id as id ,tmo.tmo_nombre as nombre
                    FROM tbl_tipo_movimiento tmo
                    INNER JOIN tbl_movimientos mov ON tmo.tmo_id = mov.tmo_id
                    WHERE 
                    tmo.estado = true AND
                    mov.estado = true AND
                    tmo.tei_id = ? AND
                    mov.sed_id = ? AND
                    mov.mov_fecha >= ? AND
                    mov.mov_fecha <= ?
                    GROUP BY tmo.tmo_id,tmo.tmo_nombre
                    ORDER BY total
                    LIMIT ?;
                    ", [
                $id_tipo_egreso_ingreso,
                $id_sede,
                $fecha_inicio->toDateString(),
                $fecha_fin->toDateString(),
                $length,
                ]
            );
        } else {
            $results = DB::select("
                    SELECT sum(mov.mov_monto) as total,count(mov.mov_monto) as cantidad , tmo.tmo_id as id ,tmo.tmo_nombre as nombre
                    FROM tbl_tipo_movimiento tmo
                    INNER JOIN tbl_movimientos mov ON tmo.tmo_id = mov.tmo_id
                    WHERE 
                    tmo.estado = true AND
                    mov.estado = true AND
                    tmo.tei_id = ? AND
                    mov.sed_id = ? AND
                    mov.mov_fecha >= ? AND
                    mov.mov_fecha <= ?
                    GROUP BY tmo.tmo_id,tmo.tmo_nombre
                    ORDER BY total;
                    ", [
                $id_tipo_egreso_ingreso,
                $id_sede,
                $fecha_inicio->toDateString(),
                $fecha_fin->toDateString(),
                ]
            );
        }
        
        return response()->json($results,200);
    }

    public function estadisticas_mensual(Request $request){
        $id_sede = intval($request->route('id_sede'));
        $validator = Validator::make($request->all(),[
            'fecha' => 'nullable | date',
            'id_tipo_egreso_ingreso' => 'nullable | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $id_tipo_egreso_ingreso = intval($request->query('id_tipo_egreso_ingreso',1));
        $fecha = $request->query('fecha',null);
        if(is_null($fecha)){
            $fecha = Carbon::now();
        } else {
            $fecha = Carbon::parse($fecha);
        }

        $fecha_inicio = $fecha->startOfMonth()->toDateString();
        $fecha_fin = $fecha->endOfMonth()->toDateString();
        $results = DB::select("
                SELECT 
                    sum(mov.mov_monto) as total,
                    count(mov.mov_monto) as cantidad,
                    sum(if(mov.tmo_id = 1,mov.mov_monto,0)) as cuota_total,
                    sum(if(mov.tmo_id = 1,1,0)) as cuota_cantidad,
                    sum(if(mov.tmo_id = 2,mov.mov_monto,0)) as matricula_total,
                    sum(if(mov.tmo_id = 2,1,0)) as matricula_cantidad,
                    sum(if(mov.tmo_id != 1 and mov.tmo_id != 2,mov.mov_monto,0)) as otros_total,
                    sum(if(mov.tmo_id != 1 and mov.tmo_id != 2,1,0)) as otros_cantidad
                FROM tbl_tipo_movimiento tmo
                INNER JOIN tbl_movimientos mov ON tmo.tmo_id = mov.tmo_id
                WHERE 
                tmo.estado = true AND
                mov.estado = true AND
                mov.tei_id = ? AND
                mov.sed_id = ? AND
                mov.mov_fecha >= ? AND
                mov.mov_fecha <= ?
                GROUP BY mov.estado;
                ", [
            $id_tipo_egreso_ingreso,
            $id_sede,
            $fecha_inicio,
            $fecha_fin,
            ]
        );
        if(count($results)>0){
            $results = $results[0];
        }
        return response()->json([
            'total' => $results->total??0,
            'cantidad' => $results->cantidad??0,
            'cuota_total' => $results->cuota_total??0,
            'cuota_cantidad' => $results->cuota_cantidad??0,
            'matricula_total' => $results->matricula_total??0,
            'matricula_cantidad' => $results->matricula_cantidad??0,
            'otros_total' => $results->otros_total??0,
            'otros_cantidad' => $results->otros_cantidad??0,
        ],200);
    }

}