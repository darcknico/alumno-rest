<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Sede;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;
use App\Models\Movimiento;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;
use App\Functions\CuentaCorrienteFunction;
use App\Functions\DiariaFunction;
use App\Functions\PlanPagoFunction;

use App\Exports\PlanPagoExport;
use App\Http\Controllers\AlumnoController;

class PlanPagoController extends Controller
{
  public function index(Request $request){
    $id_sede = $request->route('id_sede');
    $search = $request->query('search','');
    $sort = $request->query('sort','');
    $order = $request->query('order','');
    $page = $request->query('page',0);
    $length = $request->query('length',0);
    $registros = PlanPago::with([
      'inscripcion.alumno',
      'inscripcion.carrera',
    ])->where([
      'sed_id' => $id_sede,
      'estado' => 1,
    ]);
    if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $page==0 ){
      $todo = $registros->orderBy('created_at','desc')
        ->get();
      return response()->json($todo,200);
    }
    $id_departamento = $request->query('id_departamento',0);
    $id_carrera = $request->query('id_carrera',0);
    $deudores = $request->query('deudores',0); //0 TODOS - 1 SI - 2 NO

    $registros = $registros
      ->when($id_departamento>0,function($q)use($id_departamento){
        $carreras = Carrera::where([
            'dep_id' => $id_departamento,
            'estado' => 1,
          ])->pluck('car_id')->toArray();
          $inscripciones = Inscripcion::where([
            'estado' => 1,
          ])
          ->whereIn('car_id',$carreras)
          ->pluck('ins_id')->toArray();
        return $q->whereIn('ins_id',$inscripciones);
      })
      ->when($id_carrera>0,function($q)use($id_carrera){
        $inscripciones = Inscripcion::where([
            'car_id' => $id_carrera,
            'estado' => 1,
          ])
        ->pluck('ins_id')->toArray();
        return $q->whereIn('ins_id',$inscripciones);
      })
      ->when($deudores>0,function($q)use($deudores){
        if($deudores>1){
          return $q->whereNotIn('ppa_id',function($qt){
            return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
              'estado' => 1,
            ])->where('obl_saldo','>',0);
          });
        } else {
          return $q->whereIn('ppa_id',function($qt){
            return $qt->select('ppa_id')->from('tbl_obligaciones')->where([
              'estado' => 1,
            ])->where('obl_saldo','>',0);
          });
        }
      });
    $values = explode(" ", $search);
    if(count($values)>0){
      foreach ($values as $key => $value) {
        if(strlen($value)>0){
          $registros = $registros->where(function($query) use  ($value,$id_sede) {
            $query->where('ppa_matricula_monto',$value)
              ->orWhere('anio',$value)
              ->orWhere('ppa_cuota_monto',$value)
              ->orWhereHas('inscripcion',function($q)use($value,$id_sede){
                $q->whereIn('alu_id',function($qt)use($value,$id_sede){
                    $qt->select('alu_id')->from('tbl_alumnos')
                    ->where('estado',1)
                    ->where('sed_id',$id_sede)
                    ->where(function($qtz) use  ($value){
                        $qtz->where('alu_nombre','like','%'.$value.'%')
                        ->orWhere('alu_apellido','like','%'.$value.'%')
                        ->orWhere('alu_documento',$value);
                    });
                  });
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
    $total_count = $q->groupBy('sed_id')->count();
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
        'total_count'=>intval($total_count),
        'items'=>$registros,
    ],200);
  }

  public function store(Request $request){
    $id_sede = $request->route('id_sede');
    $user = Auth::user();
    $validator = Validator::make($request->all(),[
        'id_inscripcion' => 'required | integer',
        'anio' => 'required',
        'matricula_monto' => 'required',
        'cuota_monto' => 'required',
        'interes_monto' => 'required',
        'beca_porcentaje' => 'required',
        'cuota_cantidad' => 'nullable | integer',
        'fecha' => 'nullable | date',
        'dias_vencimiento' => 'nullable | integer',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $id_inscripcion = $request->input('id_inscripcion');
    $anio = $request->input('anio');
    $matricula_monto = $request->input('matricula_monto');
    $cuota_monto = $request->input('cuota_monto');
    $interes_monto = $request->input('interes_monto');
    $id_beca = $request->input('id_beca');
    $beca_nombre = $request->input('beca_nombre');
    $beca_porcentaje = $request->input('beca_porcentaje');

    $cuota_cantidad = $request->input('cuota_cantidad',10);
    $fecha = $request->input('fecha',null);
    $dias_vencimiento = $request->input('dias_vencimiento',9);


    $plan_pago = PlanPago::where([
      'estado' => 1,
      'sed_id' => $id_sede,
      'ins_id' => $id_inscripcion
    ])->where('anio',$anio)->first();
    if($plan_pago){
      return response()->json([
        'error'=>'No puede haber un plan de pago para el mismo año.',
        'anio' => true,
      ],403);
    }
    $plan_pago = new PlanPago;
    $plan_pago->id_sede = $id_sede;
    $plan_pago->id_inscripcion = $id_inscripcion;
    $plan_pago->matricula_monto = $matricula_monto;
    $plan_pago->matricula_saldo = $matricula_monto;
    $plan_pago->cuota_monto = $cuota_monto;
    $plan_pago->interes_monto = $interes_monto;
    $plan_pago->anio = $anio;
    $plan_pago->cuota_cantidad = $cuota_cantidad;
    $plan_pago->fecha = $fecha;
    $plan_pago->dias_vencimiento = $dias_vencimiento;
    $plan_pago->id_usuario = $user->id;
    $plan_pago->save();
    $detalle = PlanPagoFunction::preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje,$cuota_cantidad,$dias_vencimiento);
    $obligaciones = [];
    foreach ($detalle['obligaciones'] as $obligacion) {
      $cuota = new Obligacion;
      $cuota->id_plan_pago = $plan_pago->id;
      $cuota->id_tipo_obligacion = $obligacion['id_tipo_obligacion'];
      $cuota->descripcion = $obligacion['descripcion'];
      $cuota->monto = $obligacion['monto'];
      $cuota->saldo = $obligacion['saldo'];
      $cuota->fecha = $obligacion['fecha'];
      $cuota->id_usuario = $user->id;
      $cuota->fecha_vencimiento = $obligacion['fecha_vencimiento'];
      $cuota->save();
      $obligaciones[] = $cuota;
    }
    CuentaCorrienteFunction::armar($id_sede,$plan_pago->id);
    $plan_pago->obligaciones = $obligaciones;
    return response()->json($plan_pago,200);
  }

  public function previa(Request $request){
    $validator = Validator::make($request->all(),[
        'anio' => 'required',
        'matricula_monto' => 'required',
        'cuota_monto' => 'required',
        'beca_porcentaje' => 'required',
        'cuota_cantidad' => 'nullable | integer',
        'fecha' => 'nullable | date',
        'dias_vencimiento' => 'nullable | integer',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }

    $anio = $request->input('anio');
    $matricula_monto = $request->input('matricula_monto');
    $cuota_monto = $request->input('cuota_monto');
    $beca_porcentaje = $request->input('beca_porcentaje');

    $cuota_cantidad = $request->input('cuota_cantidad',10);
    $fecha = $request->input('fecha',null);
    $dias_vencimiento = $request->input('dias_vencimiento',9);

    $detalle = PlanPagoFunction::preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje,$cuota_cantidad,$dias_vencimiento);
    return response()->json($detalle,200);
  }

  public function update(Request $request){
    $id_sede = $request->route('id_sede');
    $id_plan_pago = $request->route('id_plan_pago');
    $user = Auth::user();
    $validator = Validator::make($request->all(),[
        'anio' => 'required',
        'matricula_monto' => 'required',
        'cuota_monto' => 'required',
        'interes_monto' => 'required',
        'beca_porcentaje' => 'required',
        'cuota_cantidad' => 'nullable | integer',
        'fecha' => 'nullable | date',
        'dias_vencimiento' => 'nullable | integer',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }

    $anio = $request->input('anio');
    $matricula_monto = $request->input('matricula_monto');
    $cuota_monto = $request->input('cuota_monto');
    $interes_monto = $request->input('interes_monto');
    $beca_porcentaje = $request->input('beca_porcentaje');

    $cuota_cantidad = $request->input('cuota_cantidad',10);
    $fecha = $request->input('fecha',null);
    $dias_vencimiento = $request->input('dias_vencimiento',9);

    $plan_pago = PlanPago::find($id_plan_pago);

    $check = PlanPago::where([
      'estado' => 1,
      'ins_id' => $plan_pago->id_inscripcion,
    ])
    ->whereNotIn('id', [$id_plan_pago])
    ->where('anio',$anio)->first();
    if($check){
      return response()->json([
        'error'=>'No puede haber un plan de pago para el mismo año.',
        'anio' => true,
      ],403);
    }

    $plan_pago->matricula_monto = $matricula_monto;
    $plan_pago->cuota_monto = $cuota_monto;
    $plan_pago->interes_monto = $interes_monto;
    $plan_pago->anio = $anio;
    $plan_pago->cuota_cantidad = $cuota_cantidad;
    $plan_pago->fecha = $fecha;
    $plan_pago->dias_vencimiento = $dias_vencimiento;
    $plan_pago->save();

    $detalle = PlanPagoFunction::preparar_obligaciones($anio,$matricula_monto,$cuota_monto,$beca_porcentaje,$cuota_cantidad,$dias_vencimiento)['obligaciones'];
    $matricula = Obligacion::where('ppa_id',$id_plan_pago)
      ->where('estado',1)
      ->where('id_tipo_obligacion',10)
      ->first();
    if($matricula){
      $matricula->descripcion = $detalle[0]['descripcion'];
      $matricula->monto = $detalle[0]['monto'];
      $matricula->saldo = $detalle[0]['saldo'];
      $matricula->fecha = $detalle[0]['fecha'];
      $matricula->fecha_vencimiento = $detalle[0]['fecha_vencimiento'];
      $matricula->save();
    } else {
      $matricula = new Obligacion;
      $matricula->id_plan_pago = $id_plan_pago;
      $matricula->id_tipo_obligacion = 10;
      $matricula->descripcion = $detalle[0]['descripcion'];
      $matricula->monto = $detalle[0]['monto'];
      $matricula->saldo = $detalle[0]['saldo'];
      $matricula->fecha = $detalle[0]['fecha'];
      $matricula->id_usuario = $user->id;
      $matricula->fecha_vencimiento = $detalle[0]['fecha_vencimiento'];
      $matricula->save();
    }

    $obligaciones = Obligacion::where('ppa_id',$id_plan_pago)
      ->where('tob_id',1)
      ->orderBy('fecha','asc')
      ->get()->toArray();
    if($cuota_cantidad>0){
      for ($i = 0; $i < $cuota_cantidad; $i++) {
        if(isset($obligaciones[$i]) ){
          $obligacion = Obligacion::find($obligaciones[$i]['id']);
          $obligacion->fecha_vencimiento = $detalle[($i+1)]['fecha_vencimiento'];
          $obligacion->fecha = $detalle[($i+1)]['fecha'];
          $obligacion->descripcion = $detalle[($i+1)]['descripcion'];
          $obligacion->monto = $detalle[($i+1)]['monto'];
          $obligacion->saldo = $detalle[($i+1)]['saldo'];
          $obligacion->estado = 1;
          $obligacion->save();
        } else {
          $cuota = new Obligacion;
            $cuota->id_plan_pago = $plan_pago->id;
            $cuota->id_tipo_obligacion = 1;
            $cuota->descripcion = $detalle[($i+1)]['descripcion'];
            $cuota->monto = $detalle[($i+1)]['monto'];
            $cuota->saldo = $detalle[($i+1)]['saldo'];
            $cuota->fecha = $detalle[($i+1)]['fecha'];
            $cuota->fecha_vencimiento = $detalle[($i+1)]['fecha_vencimiento'];
            $cuota->id_usuario = $user->id;
            $cuota->save();
        }
      }
      for ($i = $cuota_cantidad; $i < count($obligaciones); $i++) {
        $obligacion = Obligacion::find($obligaciones[$i]['id']);
        $obligacion->estado = 0;
        $obligacion->save();
      }
    } else {
      foreach ($obligaciones as $obligacion) {
        $obligacion = Obligacion::find($obligacion['id']);
        $obligacion->estado = 0;
        $obligacion->save();
      }
    }

    CuentaCorrienteFunction::armar($id_sede,$plan_pago->id);
    PlanPagoFunction::actualizar($plan_pago);

    return response()->json($plan_pago,200);
  }

  public function estadisticas(Request $request){
    $id_sede = $request->route('id_sede');
    $planes_pago = PlanPago::where([
      'estado' => 1,
      'sed_id' => $id_sede,
    ])->pluck('ppa_id')->toArray();
    $totales = \DB::table('tbl_obligaciones')
    ->selectRaw('
        sum(if(tob_id=1,obl_saldo,0)) as cuota,
        sum(if(tob_id=2,obl_saldo,0)) as interes,
        sum(if(tob_id=10,obl_saldo,0)) as matricula
        ')
    ->where([
        'estado' => 1,
    ])
    ->whereIn('ppa_id',$planes_pago)
    ->groupBy('estado')
    ->first();
    if($totales){
      $totales->total =$totales->cuota + $totales->interes + $totales->matricula;
    } else {
      $totales['total']=0;
      $totales['cuota']=0;
      $totales['interes']=0;
      $totales['matricula']=0;
    }
    $totales_hoy = \DB::table('tbl_obligaciones')
    ->selectRaw('
        sum(if(tob_id=1,obl_saldo,0)) as cuota,
        sum(if(tob_id=2,obl_saldo,0)) as interes,
        sum(if(tob_id=10,obl_saldo,0)) as matricula
        ')
    ->where([
        'estado' => 1,
    ])
    ->whereDate('obl_fecha_vencimiento','<=',Carbon::now())
    ->whereIn('ppa_id',$planes_pago)
    ->groupBy('estado')
    ->first();
    if($totales_hoy){
      $totales_hoy->total=$totales_hoy->cuota + $totales_hoy->interes + $totales_hoy->matricula;
    } else {
      $totales_hoy['total']=0;
      $totales_hoy['cuota']=0;
      $totales_hoy['interes']=0;
      $totales_hoy['matricula']=0;
    }
    return response()->json([
        'totales' => $totales,
        'totales_hoy' => $totales_hoy,
    ], 200);
  }

  public function obligacion_siguiente(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $id_tipo_obligacion = $request->query('id_tipo_obligacion',1);
    $todo = Obligacion::with('tipo')->where([
      'ppa_id' => $id_plan_pago,
      'tob_id' => $id_tipo_obligacion,
      'estado' => 1,
    ])->where('saldo','>',0)->orderBy('fecha_vencimiento','asc')->first();

    return response()->json($todo,200);
  }

  public function cuenta_corriente(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $todo = Obligacion::with('tipo','usuario','obligacion')->where([
      'ppa_id' => $id_plan_pago,
      'estado' => 1,
    ])
    ->where('obl_monto','>',0)
    ->whereIn('tob_id',[1,2,3,4])
    ->orderByRaw('obl_fecha_vencimiento asc,tob_id asc')->get();
    return response()->json($todo,200);
  }

  public function rearmar(Request $request){
    $id_sede = $request->route('id_sede');
    $id_plan_pago = $request->route('id_plan_pago');
    $todo = CuentaCorrienteFunction::armar($id_sede,$id_plan_pago);
    return response()->json($todo,200);
  }
  public function rearmar_todo(Request $request){
    $sedes = Sede::where('estado',1)->get();
    $salida = [];
    foreach ($sedes as $sede) {
      $planes = PlanPago::where([
        'estado' => 1,
        'sed_id' => $sede->id,
      ])->get();
      foreach ($planes as $plan) {
        $todo = CuentaCorrienteFunction::armar($sede->id,$plan->id);
        $salida[]=$plan;
      }
    }
    return response()->json($salida,200);
  }

  public function show(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $todo = PlanPago::with(
      'usuario',
      'inscripcion',
      'inscripcion.carrera',
      'inscripcion.modalidad',
      'inscripcion.alumno'
    )->find($id_plan_pago);
    return response()->json($todo,200);
  }

  public function pagos(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $obligaciones = Obligacion::where('ppa_id',$id_plan_pago)->pluck('obl_id')->toArray();
    $todo = Pago::with('usuario','tipo')
    ->whereIn('obl_id',$obligaciones)
    ->orderBy('fecha','desc')
    ->get();
    return response()->json($todo,200);
  }

  public function cuotas(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $todo = Obligacion::with('interes')->where([
      'estado'=>1,
      'ppa_id' => $id_plan_pago,
      'tob_id' => 1,
    ])->orderBy('fecha_vencimiento','asc')->get();
    return response()->json($todo,200);
  }

  public function matricula(Request $request){
    $id_plan_pago = $request->route('id_plan_pago');
    $todo = Obligacion::where([
      'estado'=>1,
      'ppa_id' => $id_plan_pago,
      'tob_id' => 10,
    ])->orderBy('fecha_vencimiento','asc')->first();
    return response()->json($todo,200);
  }

  public function pagar(Request $request){
    $user = Auth::user();
    $id_plan_pago = $request->route('id_plan_pago');
    $id_sede = $request->route('id_sede');
    $sede = Sede::find($id_sede);
    $plan_pago_precio = CuentaCorrienteFunction::ultimo_precio_plan($id_sede);

    $validator = Validator::make($request->all(),[
      'monto' => 'required | numeric',
      'fecha' => 'required | date',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $bonificar_intereses = $request->input('bonificar_intereses',false);
    $monto = round($request->input('monto'),2);
    $descripcion = $request->input('descripcion','');
    $numero_oficial = $request->input('numero_oficial');
    $id_movimiento = $request->input('id_movimiento',0);

    $movimiento = Pago::where('id_movimiento',$id_movimiento)->first();
    if($movimiento and $id_movimiento>0){
        return response()->json(['error'=>'El movimiento se encuentra en uso'],403);
    }
    $saldo = $monto;
    $fecha = new Carbon($request->input('fecha'));
    if($bonificar_intereses){
      $detalles = $this->detallePreparar($id_plan_pago,2,$fecha,$saldo,true);
    } else {
      $detalles = $this->detallePreparar($id_plan_pago,1,$fecha,$saldo,true);
    }
    $plan_pago = PlanPago::find($id_plan_pago);

    $obligacion = new Obligacion;
    $obligacion->monto = $monto;
    $obligacion->descripcion = $descripcion;
    $obligacion->saldo = 0;
    $obligacion->fecha = $fecha->toDateString();
    $obligacion->fecha_vencimiento = $fecha->toDateString();
    $obligacion->ppa_id = $id_plan_pago;
    $obligacion->tob_id = 3;
    $obligacion->id_usuario = $user->id;
    $obligacion->save();

    $numero = $sede->pago_numero + 1;
    $pago = new Pago;
    $pago->fecha = $fecha->toDateString();
    $pago->monto = $monto;
    $pago->descripcion = $descripcion;
    $pago->id_usuario = $user->id;
    $pago->ppa_id = $id_plan_pago;
    $pago->obl_id = $obligacion->obl_id;
    $pago->id_sede = $id_sede;
    $pago->id_movimiento = $id_movimiento;
    $pago->id_inscripcion = $plan_pago->id_inscripcion;
    $pago->numero_oficial = $numero_oficial;
    $pago->numero = $numero;
    $pago->save();
    $sede->pago_numero = $numero;
    $sede->save();

    foreach ($detalles as $detalle) {
      $parcial = new ObligacionPago;
      $parcial->opa_monto = $detalle['pagado'];
      $parcial->obl_id = $detalle['id_obligacion'];
      $parcial->pag_id = $pago->pag_id;
      $parcial->id_usuario = $user->id;
      $parcial->save();
      $obligacion = Obligacion::where('obl_id',$detalle['id_obligacion'])->first();
      $saldo = $obligacion->saldo - $detalle['pagado'];
      if($saldo<0){
        $saldo = 0;
      }
      $obligacion->saldo = $saldo;
      $obligacion->save();
      if($obligacion->tob_id == 1 and $saldo > 0){
        if($detalle['bonificado']){
          $sede = Sede::find($id_sede);
          $descripcion = "Bonificacion adelanto - ".$obligacion->descripcion;
          $obligacion_bonificado = new Obligacion;
          $obligacion_bonificado->monto = $plan_pago_precio->bonificacion_monto;
          $obligacion_bonificado->descripcion = $descripcion;
          $obligacion_bonificado->saldo = 0;
          $obligacion_bonificado->fecha = $fecha->toDateString();
          $obligacion_bonificado->fecha_vencimiento = $fecha->toDateString();
          $obligacion_bonificado->ppa_id = $id_plan_pago;
          $obligacion_bonificado->tob_id = 4;
          $obligacion_bonificado->id_usuario = $user->id;
          $obligacion_bonificado->save();

          $numero = $sede->pago_numero + 1;
          $pago_bonificado = new Pago;
          $pago_bonificado->fecha = $fecha->toDateString();
          $pago_bonificado->monto = $plan_pago_precio->bonificacion_monto;
          $pago_bonificado->descripcion = $descripcion;
          $pago_bonificado->id_usuario = $user->id;
          $pago_bonificado->ppa_id = $id_plan_pago;
          $pago_bonificado->obl_id = $obligacion_bonificado->obl_id;
          $pago_bonificado->id_sede = $id_sede;
          $pago_bonificado->id_movimiento = 0;
          $pago_bonificado->id_inscripcion = $plan_pago->id_inscripcion;
          $pago_bonificado->id_tipo_pago = 2;
          $pago_bonificado->numero = $numero;
          $pago_bonificado->save();
          $sede->pago_numero = $numero;
          $sede->save();

          $parcial_bonificado = new ObligacionPago;
          $parcial_bonificado->opa_monto = $plan_pago_precio->bonificacion_monto;
          $parcial_bonificado->obl_id = $obligacion->id;
          $parcial_bonificado->pag_id = $pago_bonificado->pag_id;
          $parcial_bonificado->id_usuario = $user->id;
          $parcial_bonificado->save();
          $obligacion = Obligacion::where('obl_id',$obligacion->id)->first();
          $obligacion->saldo = $obligacion->saldo - $plan_pago_precio->bonificacion_monto;
          $obligacion->save();
        }
        CuentaCorrienteFunction::interes_calcular($obligacion->obl_id);
      }
    }
    PlanPagoFunction::actualizar($plan_pago);
    return response()->json($pago,200);
  }

  public function pagoPreparar(Request $request){
    $id_sede = $request->route('id_sede');
    $id_plan_pago = $request->route('id_plan_pago');

    $validator = Validator::make($request->all(),[
      'monto' => 'required',
      'fecha' => 'required',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $bonificar_intereses = $request->input('bonificar_intereses',false);
    $monto = round($request->input('monto'),2);
    $fecha = Carbon::parse($request->input('fecha'));
    $saldo = $monto;
    if($bonificar_intereses){
      $detalles = $this->detallePreparar($id_plan_pago,2,$fecha,$saldo);
    } else {
      $detalles = $this->detallePreparar($id_plan_pago,1,$fecha,$saldo);
    }

    return response()->json([
      'monto' => $monto,
      'saldo' => $saldo,
      'detalles' => $detalles,
    ],200);
  }

  public function bonificar(Request $request){
    $user = Auth::user();
    $id_plan_pago = $request->route('id_plan_pago');
    $id_sede = $request->route('id_sede');
    $sede = Sede::find($id_sede);

    $validator = Validator::make($request->all(),[
      'monto' => 'required',
      'fecha' => 'required',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $id_tipo_pago = $request->input('id_tipo_pago',false);
    $monto = round($request->input('monto'),2);
    $descripcion = $request->input('descripcion','');
    $saldo = $monto;
    $fecha = new Carbon($request->input('fecha'));
    $detalles = $this->detallePreparar($id_plan_pago,$id_tipo_pago,$fecha,$saldo,true);

    $plan_pago = PlanPago::find($id_plan_pago);

    $obligacion = new Obligacion;
    $obligacion->monto = $monto;
    $obligacion->descripcion = $descripcion;
    $obligacion->saldo = 0;
    $obligacion->fecha = $fecha->toDateString();
    $obligacion->fecha_vencimiento = $fecha->toDateString();
    $obligacion->ppa_id = $id_plan_pago;
    $obligacion->tob_id = 4;
    $obligacion->id_usuario = $user->id;
    $obligacion->save();

    $numero = $sede->pago_numero + 1;
    $pago = new Pago;
    $pago->fecha = $fecha->toDateString();
    $pago->monto = $monto;
    $pago->descripcion = $descripcion;
    $pago->id_usuario = $user->id;
    $pago->ppa_id = $id_plan_pago;
    $pago->obl_id = $obligacion->obl_id;
    $pago->id_sede = $id_sede;
    $pago->id_movimiento = 0;
    $pago->id_inscripcion = $plan_pago->id_inscripcion;
    $pago->id_tipo_pago = $id_tipo_pago;
    $pago->numero = $numero;
    $pago->save();
    $sede->pago_numero = $numero;
    $sede->save();

    foreach ($detalles as $detalle) {
      $parcial = new ObligacionPago;
      $parcial->opa_monto = $detalle['pagado'];
      $parcial->obl_id = $detalle['id_obligacion'];
      $parcial->pag_id = $pago->pag_id;
      $parcial->id_usuario = $user->id;
      $parcial->save();
      $obligacion = Obligacion::where('obl_id',$detalle['id_obligacion'])->first();
      $obligacion->saldo = $obligacion->saldo - $detalle['pagado'];
      $obligacion->save();
      if($obligacion->tob_id == 1){
        CuentaCorrienteFunction::interes_calcular($obligacion->obl_id);
      }
    }
    PlanPagoFunction::actualizar($plan_pago);
    return response()->json($pago,200);
  }

  public function bonificarPreparar(Request $request){
    $id_sede = $request->route('id_sede');
    $id_plan_pago = $request->route('id_plan_pago');

    $validator = Validator::make($request->all(),[
      'monto' => 'required',
      'fecha' => 'required',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $id_tipo_pago = $request->input('id_tipo_pago',false);
    $monto = round($request->input('monto'),2);
    $fecha = Carbon::parse($request->input('fecha'));
    $saldo = $monto;
    $detalles = $this->detallePreparar($id_plan_pago,$id_tipo_pago,$fecha,$saldo);

    return response()->json([
      'monto' => $monto,
      'saldo' => $saldo,
      'detalles' => $detalles,
    ],200);
  }

  public static function detallePreparar($id_plan_pago,$id_tipo_pago,$fecha,&$monto,$aplicar = false){
    $plan_pago = PlanPago::find($id_plan_pago);
    $plan_pago_precio = CuentaCorrienteFunction::ultimo_precio_plan($plan_pago->id_sede);
    switch ($id_tipo_pago) {
      case 1:
        $tipo_pagos=[1]; //CUOTA
        break;
      case 2:
        $tipo_pagos=[1]; //BONIFICAR CUOTAS
        break;
      case 3:
        $tipo_pagos=[2]; //BONIFICAR INTERESES
        break;
      default:
        $tipo_pagos=[1,2];
        break;
    }
    $detalles = [];
    $obligaciones = Obligacion::with('tipo','obligacion.tipo')->where([
      'ppa_id'=>$id_plan_pago,
      'estado'=>1,
    ])
    ->whereIn('tob_id',$tipo_pagos)
    ->where('obl_saldo','>',0)
    ->orderByRaw('obl_fecha_vencimiento asc,tob_id asc')
    ->get();
    foreach ($obligaciones as $obl) {
      $obligacion = Obligacion::where('obl_id',$obl->obl_id)->first();
      $monto_actual = round($obligacion->monto,2);
      $saldo_actual = round($obligacion->saldo,2);
      $fecha_vencimiento = Carbon::parse($obligacion->fecha_vencimiento);
      $bonificado = false;
      if($fecha<=$fecha_vencimiento->subDays(5) 
        and ($id_tipo_pago == 1 or $id_tipo_pago == 2) 
        and $obligacion->tob_id == 1 
        and ($monto - $saldo_actual + $plan_pago_precio->bonificacion_monto)>=0 ){
        $saldo_actual = $saldo_actual - $plan_pago_precio->bonificacion_monto;
        if($saldo_actual<0){
          $saldo_actual = 0;
        }
        $bonificado = true;
      }
      $saldo = round($saldo_actual - $monto,2);
      if($saldo >= 0){
        if($saldo > 0 and $id_tipo_pago == 3){
          break;
        }
        $detalles[]=[
          'id_obligacion' => $obligacion->obl_id,
          'monto' => $monto_actual,
          'haber' => $saldo_actual,
          'pagado' => round($monto,2),
          'saldo' => round($saldo,2),
          'obligacion' => $obl,
          'bonificado' => $bonificado,
        ];
        $monto = 0;
        break;
      } else {
        $detalles[]=[
          'id_obligacion' => $obligacion->id,
          'monto' => $monto_actual,
          'haber' => $saldo_actual, 
          'pagado' => $saldo_actual,
          'saldo' => 0,
          'obligacion' => $obl,
          'bonificado' => $bonificado,
        ];
        $monto = $monto - $saldo_actual;
        $obl = Obligacion::with('tipo','obligacion.tipo')->where('obl_id_obligacion',$obligacion->id)->first();
        if($id_tipo_pago == 1 and $obl){
          try {
            \DB::beginTransaction();
            $pago = new Pago;
            $pago->pag_fecha = $fecha->toDateString();
            $pago->pag_monto = 0;
            $pago->pag_descripcion = '';
            $pago->ppa_id = $obligacion->ppa_id;
            $pago->obl_id = 0;
            $pago->sed_id = 0;
            $pago->id_usuario = 0;
            $pago->id_movimiento = 0;
            $pago->save();
            $parcial = new ObligacionPago;
            $parcial->opa_monto = $saldo_actual;
            $parcial->obl_id = $obligacion->id;
            $parcial->pag_id = $pago->id;
            $parcial->id_usuario = 0;
            $parcial->save();
            $interes = CuentaCorrienteFunction::interes_calcular($obligacion->id,false);
            if(!is_null($interes)){
              $monto_actual = round($interes['obl_monto'],2);
              $saldo_actual = round($interes['obl_saldo'],2);
            } else {
              $monto_actual = 0;
              $saldo_actual = 0;
            }
            \DB::rollBack();
          } catch (\PDOException $e) {
            $monto_actual = 0;
            $saldo_actual = 0;
            \DB::rollBack();
          }
          $saldo = round($saldo_actual - $monto,2);
          if($saldo >= 0){
            if($saldo_actual == 0 ){
              break;
            }
            $detalles[]=[
              'id_obligacion' => $obl->obl_id,
              'monto' => $monto_actual,
              'haber' => $saldo_actual,
              'pagado' => round($monto,2),
              'saldo' => round($saldo,2),
              'obligacion' => $obl,
              'bonificado' => $bonificado,
            ];
            break;
          } else {
            $detalles[]=[
              'id_obligacion' => $obl->obl_id,
              'monto' => $monto_actual,
              'haber' => $saldo_actual, 
              'pagado' => $saldo_actual,
              'saldo' => 0,
              'obligacion' => $obl,
              'bonificado' => $bonificado,
            ];
            $monto = $monto - $saldo_actual;
          }
        }
      }
    }
    return $detalles;
  }


  public function pagar_matricula(Request $request){
    $user = Auth::user();
    $id_sede = $request->route('id_sede');
    $id_plan_pago = $request->route('id_plan_pago');
    $validator = Validator::make($request->all(),[
      'monto' => 'required',
    ]);
    if($validator->fails()){
      return response()->json(['error'=>$validator->errors()],403);
    }
    $monto = $request->input('monto');
    $descripcion = $request->input('descripcion','');
    $id_movimiento = $request->input('id_movimiento',0);

    $plan_pago = PlanPago::find($id_plan_pago);
    if($plan_pago->matricula_saldo<$monto){
      return response()->json(['error'=>'El saldo es menor al monto ingresado.'],403);
    }

    $fecha = Carbon::now();

    $obligacion = new Obligacion;
    $obligacion->monto = $monto;
    $obligacion->descripcion = $descripcion;
    $obligacion->saldo = 0;
    $obligacion->fecha = $fecha->toDateString();
    $obligacion->fecha_vencimiento = $fecha->toDateString();
    $obligacion->ppa_id = $id_plan_pago;
    $obligacion->tob_id = 11;
    $obligacion->id_usuario = $user->id;
    $obligacion->save();

    $sede = Sede::find($id_sede);
    $numero = $sede->pago_numero + 1;
    $pago = new Pago;
    $pago->fecha = $fecha->toDateString();
    $pago->monto = $monto;
    $pago->descripcion = $descripcion;
    $pago->id_usuario = $user->id;
    $pago->ppa_id = $id_plan_pago;
    $pago->obl_id = $obligacion->obl_id;
    $pago->id_sede = $id_sede;
    $pago->id_movimiento = $id_movimiento;
    $pago->id_inscripcion = $plan_pago->id_inscripcion;
    $pago->id_tipo_pago = 10;
    $pago->numero = $numero;
    $pago->save();
    $sede->pago_numero = $numero;
    $sede->save();

    $parcial = new ObligacionPago;
    $parcial->monto = $monto;
    $parcial->id_obligacion = $obligacion_matricula->id;
    $parcial->id_pago = $pago->id;
    $parcial->id_usuario = $user->id;
    $parcial->save();

    PlanPagoFunction::actualizar($plan_pago);

    return response()->json($pago,200);
  }

  public function exportar(Request $request){
    $id_sede = $request->route('id_sede');
    $search = $request->query('search','');
    $id_departamento = $request->query('id_departamento',0);
    $id_carrera = $request->query('id_carrera',0);
    $deudores = $request->query('deudores',0);

    return (new PlanPagoExport($id_sede,$search,$id_departamento,$id_carrera,$deudores))->download('pagos.xlsx');
  }


  public function destroy(Request $request){
    $user = Auth::user();
    $id_plan_pago = $request->route('id_plan_pago');

    $plan_pago = PlanPago::find($id_plan_pago);

    $pagos = Pago::where('id_plan_pago',$plan_pago)->where('estado',1)->get();
    foreach ($pagos as $pago) {
      $pago = Pago::find($pago->id);
      $pago->estado = 0;
      $pago->save();
      $movimiento = Movimiento::find($pago->id_movimiento);
      $movimiento->estado = 0;
      $movimiento->usu_id_baja = $user->id;
      $movimiento->deleted_at = Carbon::now();
      $movimiento->save();
      DiariaFunction::quitar($id_sede,$id_movimiento);
    }
    $obligaciones = Obligacion::where('id_plan_pago',$id_plan_pago)->where('estado',1)->get();
    foreach ($obligaciones as $obligacion) {
      $obligacion = Obligacion::find($obligacion->id);
      $obligacion->estado = 0;
      $obligacion->save();
      ObligacionPago::where([
        'obl_id' => $obligacion->id,
        'estado' => 1,
      ])->update([
        'estado' => 0
      ]);
      ObligacionInteres::where([
        'obl_id' => $obligacion->id,
        'estado' => 1,
      ])->update([
        'estado' => 0
      ]);
    }

    $plan_pago->estado = 0;
    $plan_pago->usu_id_baja = $user->id;
    $plan_pago->deleted_at = Carbon::now();
    $plan_pago->save();

    return response()->json($plan_pago,200);
  }
}
