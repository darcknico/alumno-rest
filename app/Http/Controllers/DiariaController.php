<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\Movimiento;
use App\Models\Diaria;
use App\Models\Pago;

use App\Exports\DiariaExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use App\Functions\DiariaFunction;
use Carbon\Carbon;

class DiariaController extends Controller{

	public function index(Request $request){
		$id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = Diaria::where([
        	'estado' => 1,
        	'sed_id' => $id_sede
        ]);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }

    	$values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  //$query->whereRaw("DATE_FORMAT(mov_fecha, '%Y') ",'like','%'.$value.'%');
                });
              }
            }
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('fecha_inicio','desc');
        }
        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $estadisticas = $q->selectRaw('
        	count(*) as total_cantidad
        	')->groupBy('sed_id')->first();
        if($length>0){
        $registros = $registros->limit($length);
        if($start>1){
            $registros = $registros->offset($start)->get();
        } else {
            $registros = $registros->get();
        }

        } else {
            $registros = $registros->get();
        }

        return response()->json([
            'total_count'=>intval($estadisticas->total_cantidad??0),
            'items'=>$registros,
        ],200);
	}

    public function ultimos(Request $request){
        $id_sede = $request->route('id_sede');
        $diarias = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->orderBy('fecha_inicio','desc')->limit(5)->get();
        return response()->json($diarias,200);
    }

    public function show(Request $request){
        $id_sede = $request->route('id_sede');
        $id_diaria = $request->route('id_diaria');

        $diaria = Diaria::find($id_diaria);

        $ingresos = Movimiento::with('tipo','forma')->where([
            'estado' => 1,
            'tei_id' => 1,
            'sed_id' => $id_sede
        ])
        ->whereDate('fecha','>=',$diaria->fecha_inicio)
        ->when(!empty($diaria->fecha_fin),function($q)use($diaria){
        	return $q->whereDate('fecha','<=',$diaria->fecha_fin);
        })
        ->get();
        $movimientos = [];
        foreach ($ingresos as $movimiento) {
            $pago = Pago::where('mov_id',$movimiento->id)->first();
            if($pago){
                if($pago->plan_pago){
                    $movimiento['alumno'] = $pago->plan_pago->inscripcion->alumno;
                } else {
                    $movimiento['alumno'] = $pago->inscripcion->alumno;
                }
                
            }
            $movimientos[] = $movimiento;
        }

        $egresos = Movimiento::with('tipo','forma')->where([
            'estado' => 1,
            'tei_id' => 0,
            'sed_id' => $id_sede
        ])
        ->whereDate('fecha','>=',$diaria->fecha_inicio)
        ->when(!empty($diaria->fecha_fin),function($q)use($diaria){
        	return $q->whereDate('fecha','<=',$diaria->fecha_fin);
        })
        ->get();

        $diaria['ingresos'] = $movimientos;
        $diaria['egresos'] = $egresos;

        return response()->json($diaria,200);
    }

    public function store(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $fecha_inicio = Carbon::parse($request->input('fecha_inicio'));

        $diaria = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','>=',$fecha_inicio)
        ->whereNotNull('fecha_fin')
        ->orderBy('fecha_inicio','asc')
        ->first();
        if($diaria){
            $fecha_inicio_actual = Carbon::parse($diaria->fecha_inicio);
            if($fecha_inicio >= $fecha_inicio_actual){
                return response()->json(['error'=>'La fecha esta ocupando otra diaria.'],403);
            }
        }

        $diaria = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_fin','<=',$fecha_inicio)
        ->orderBy('fecha_inicio','desc')
        ->first();
        if($diaria){
            $fecha_fin_actual = Carbon::parse($diaria->fecha_fin);
            if($fecha_inicio <= $fecha_fin_actual){
                return response()->json(['error'=>'La fecha esta ocupando otra diaria.'],403);
            }
        }

        $diaria = new Diaria;
        $diaria->id_sede = $id_sede;
        $diaria->fecha_inicio = $fecha_inicio;
        $diaria->id_usuario = $user->id;
        $diaria->save();
        $diaria = DiariaFunction::actualizar_diaria($diaria);
        DiariaFunction::actualizar($id_sede,$fecha_inicio);
        return response()->json($diaria,200);
    }

    public function update(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_diaria = $request->route('id_diaria');
        $validator = Validator::make($request->all(),[
            'fecha_fin' => 'required | date',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $diaria = Diaria::find($id_diaria);
        $fecha_fin = Carbon::parse($request->input('fecha_fin'));
        $fecha_inicio = Carbon::parse($request->fecha_inicio);

        $ultimo = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','>',$fecha_inicio)
        ->orderBy('fecha_inicio','asc')
        ->first();
        if($ultimo){
            $fecha_inicio_actual = Carbon::parse($ultimo->fecha_inicio);
            if($fecha_fin >= $fecha_inicio_actual){
                return response()->json(['error'=>'La fecha esta ocupando otra diaria.'],403);
            }
        }

        if($fecha_inicio > $fecha_fin){
            return response()->json(['error'=>'La fecha inicial debe ser menor o igual a la fecha final.'],403);
        }

        $diaria->fecha_fin = $fecha_fin;
        $diaria->cierre_id_usuario = $user->id;
        $diaria->save();
        $diaria = DiariaFunction::actualizar_diaria($diaria);
        DiariaFunction::actualizar($id_sede,$fecha_fin);
        return response()->json($diaria,200);
    }

    public function destroy(Request $request){
        $id_sede = $request->route('id_sede');
        $id_diaria = $request->route('id_diaria');

        $diaria = Diaria::find($id_diaria);
        $diaria->estado = 0;
        $diaria->save();
        DiariaFunction::actualizar($id_sede,$diaria->fecha_inicio);
        return response()->json($diaria,200);
    }

    public function ingresos(Request $request){
    	$id_diaria = $request->route('id_diaria');
    	$diaria = Diaria::find($id_diaria);

        $todo = Movimiento::with('tipo','forma')->where([
            'estado' => 1,
            'tei_id' => 1,
            'sed_id' => $id_sede
        ])
        ->whereDate('fecha','>=',$diaria->fecha_inicio)
        ->when(!empty($diaria->fecha_fin),function($q)use($diaria){
        	return $q->whereDate('fecha','<=',$diaria->fecha_fin);
        })
        ->get();
        $movimientos = [];
        foreach ($todo as $movimiento) {
            $pago = Pago::where('mov_id',$movimiento->id)->first();
            if($pago){
                $movimiento['alumno'] = $pago->plan_pago->inscripcion->alumno;
            }
            $movimientos[] = $movimiento;
        }
        return response()->json($movimientos,200);
    }

    public function egresos(Request $request){
    	$id_diaria = $request->route('id_diaria');
    	$diaria = Diaria::find($id_diaria);

        $todo = Movimiento::with('tipo','forma')->where([
            'estado' => 1,
            'tei_id' => 0,
            'sed_id' => $id_sede
        ])
        ->whereDate('fecha','>=',$diaria->fecha_inicio)
        ->when(!empty($diaria->fecha_fin),function($q)use($diaria){
        	return $q->whereDate('fecha','<=',$diaria->fecha_fin);
        })
        ->get();
        return response()->json($todo,200);
    }

    public function abrir(Request $request){
        $pass = $request->query('pass');
        $diarias = [];
        if($pass=="AdSaavedra"){
            $sedes = Sede::where('estado',1)->get();
            $ahora = Carbon::now();
            foreach ($sedes as $sede) {
                $diaria = DiariaFunction::abrir($sede->id,$ahora);
                $diarias[]=$diaria;
            }
        }
        
        return response()->json($diarias,200);
    }

    public function cerrar(Request $request){
        $pass = $request->query('pass');
        $diarias = [];
        if($pass=="AdSaavedra"){
            $sedes = Sede::where('estado',1)->get();
            $ahora = Carbon::now();
            foreach ($sedes as $sede) {
                $diaria = DiariaFunction::cerrar($sede->id,$ahora);
                $diarias[]=$diaria;
            }
        }
        return response()->json($diarias,200);
    }

    public function rearmar(Request $request){
        $sedes = Sede::where('estado',1)->get();
        foreach ($sedes as $sede) {
            DiariaFunction::actualizar($sede->id);
        }
        return response()->json($sedes,200);
    }

    public function siguiente(Request $request){
        $id_sede = $request->route('id_sede');
        $id_diaria = $request->route('id_diaria');

        $diaria = Diaria::find($id_diaria);
        $siguiente = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','>',$diaria->fecha_inicio)
        ->orderBy('fecha_inicio','asc')
        ->first();

        $ultimo = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','>',$diaria->fecha_inicio)
        ->orderBy('fecha_inicio','desc')
        ->first();
        return response()->json([
            'next' => $siguiente,
            'last' => $ultimo,
        ],200);
    }

    public function anterior(Request $request){
        $id_sede = $request->route('id_sede');
        $id_diaria = $request->route('id_diaria');

        $diaria = Diaria::find($id_diaria);
        $anterior = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','<',$diaria->fecha_inicio)
        ->orderBy('fecha_inicio','desc')
        ->first();

        $semana_pasada = Carbon::parse($diaria->fecha_inicio)->subWeek();
        $semana = Diaria::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])
        ->whereDate('fecha_inicio','<=',$semana_pasada)
        ->orderBy('fecha_inicio','desc')
        ->first();
        return response()->json([
            'next' => $anterior,
            'last' => $semana,
        ],200);
    }

    public function exportar(Request $request){
        $id_diaria = $request->route('id_diaria');
        $diaria = Diaria::find($id_diaria);
        $export = new DiariaExport($id_diaria);
        $export->custom();
        $fecha = Carbon::parse($diaria->fecha);
        return $export->download('diaria'.$fecha->format('Y-m-d').'.xlsx');
    }
}