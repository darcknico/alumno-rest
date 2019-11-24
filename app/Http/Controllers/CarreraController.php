<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\PlanEstudio;
use App\Models\Modalidad;
use App\Models\Departamento;
use App\Models\Sede;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class CarreraController extends Controller
{
    /**
    * @OA\Get(
    *     path="/carreras",
    *     tags={"Carreras"},
    *     summary="Listado de carreras",
    *     description="Mostrar todas las carreras",
    *     operationId="index",
    *     @OA\Response(
    *         response=200,
    *         description="Mostrar todas las carreras."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function index(Request $request)
    {

        $id_departamento = $request->route('id_departamento',null);

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = Carrera::with('departamento','plan_estudio')->where([
            'estado' => 1 ,
        ]);
        if(is_null($id_departamento)){
            $departamentos = Departamento::where([
                'estado' => 1,
            ])->pluck('dep_id')->toArray();
            $registros = $registros->whereIn('dep_id',$departamentos);
        } else {
            $registros = $registros->where('dep_id',$id_departamento);
        }
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('nombre','desc')
            ->get();
            return response()->json($todo,200);
        }
        $id_departamento = $request->query('id_departamento',0);
        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                return $q->where('dep_id',$id_departamento);
            });
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->where('car_nombre','like','%'.$value.'%')
                    ->orWhere('car_nombre_corto','like','%'.$value.'%')
                    ->orWhere('car_descripcion','like','%'.$value.'%')
                    ->orWhere('car_titulo','like','%'.$value.'%');
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
        $total_count = $q->groupBy('estado')->count();
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
            'total_count'=>intval($total_count),
            'items'=>$registros,
        ],200);
    }

    public function estadisticas(Request $request){

        $departamentos = \DB::table('tbl_departamentos')
        ->selectRaw('
            tbl_departamentos.dep_id as id,
            tbl_departamentos.dep_nombre as nombre,
            count(tbl_carreras.car_id) as total
            ')
        ->rightJoin('tbl_carreras','tbl_departamentos.dep_id','tbl_carreras.dep_id')
        ->where([
            'tbl_departamentos.estado' => 1,
            'tbl_carreras.estado' => 1,
        ])
        ->groupBy(\DB::raw('id,nombre'))
        ->orderByRaw('count(tbl_carreras.car_id) desc')
        ->get();
        $carreras = \DB::table('tbl_carreras')
        ->selectRaw('
            tbl_carreras.car_id as id,
            tbl_carreras.car_nombre as nombre,
            count(tbl_inscripciones.ins_id) as total
            ')
        ->rightJoin('tbl_inscripciones','tbl_carreras.car_id','tbl_inscripciones.car_id')
        ->where([
            'tbl_carreras.estado' => 1, 
            'tbl_inscripciones.estado' => 1, 
        ])
        ->groupBy(\DB::raw('id,nombre'))
        ->orderByRaw('count(tbl_inscripciones.ins_id) desc')
        ->get();

        $totales = \DB::table('tbl_departamentos')
        ->selectRaw('
            count(tbl_carreras.car_id) as total
            ')
        ->rightJoin('tbl_carreras','tbl_departamentos.dep_id','tbl_carreras.dep_id')
        ->where([
            'tbl_departamentos.estado' => 1,
            'tbl_carreras.estado' => 1,        ])
        ->groupBy('tbl_departamentos.sed_id')
        ->first();
        if(!$totales){
            $totales['total'] = 0;
        }

        $totales_hoy = \DB::table('tbl_departamentos')
        ->selectRaw('
            count(tbl_carreras.car_id) as total
            ')
        ->rightJoin('tbl_carreras','tbl_departamentos.dep_id','tbl_carreras.dep_id')
        ->where([
            'tbl_departamentos.estado' => 1,
            'tbl_carreras.estado' => 1,
        ])
        ->whereYear('tbl_carreras.created_at',Carbon::now()->year)
        ->groupBy('tbl_departamentos.sed_id')
        ->first();
        if(!$totales_hoy){
            $totales_hoy['total'] = 0;
        }
        
        return response()->json([
            'departamentos' => $departamentos,
            'carreras' => $carreras,
            'totales' => $totales,
            'totales_hoy' => $totales_hoy,
        ], 200);
    }

    /**
    * @OA\Post(
    *     path="/carreras",
    *     tags={"Carreras"},
    *     summary="Nueva carrera",
    *     description="Guardar nueva carrera",
    *     operationId="create",
    *     @OA\RequestBody(
    *          description="Datos para crear una nueva carrera",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola carrera.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    *     @OA\Response(
    *         response="403",
    *         description="Error Validator."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function store(Request $request)
    {
        $id_departamento = $request->route('id_departamento',null);
        if(is_null($id_departamento)){
            $id_departamento = $request->input('id_departamento');
        }
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'nombre_corto' => 'required',
            'titulo' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $nombre = $request->input('nombre');
        $nombre_corto = $request->input('nombre_corto');
        $titulo = $request->input('titulo');
        $descripcion = $request->input('descripcion');
        $modalidades = $request->input('modalidades',[]);

        $todo = new Carrera;
        $todo->car_nombre = $nombre;
        $todo->car_nombre_corto = $nombre_corto;
        $todo->car_descripcion = $descripcion;
        $todo->car_titulo = $titulo;
        $todo->dep_id = $id_departamento;
        $todo->usu_id = $user->id;
        $todo->save();

        foreach ($modalidades as $modalidad) {
            $carmod = new CarreraModalidad;
            $carmod->car_id = $todo->id;
            $carmod->mod_id = $modalidad['id_modalidad'];
            $carmod->usu_id = $user->id;
            $carmod->save();
        }
        return response()->json($todo,200);
    }

    public function modalidad_asociar(Request $request){
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');
        $id_modalidad = $request->route('id_modalidad');

        $carrera = Carrera::find($id_carrera);
        $modalidad = Modalidad::find($id_modalidad);
        if($carrera and $modalidad){
            $todo = CarreraModalidad::where([
                'estado' => 1,
                'car_id' => $id_carrera,
                'mod_id' => $id_modalidad
            ])->first();
            if($todo){
                $todo->usu_id = $user->id;
                $todo->save();
            } else {
                $todo = new CarreraModalidad;
                $todo->car_id = $id_carrera;
                $todo->mod_id = $id_modalidad;
                $todo->usu_id = $user->id;
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Carrera o el Tipo de Modalidad',
        ],404);
    }

    public function modalidad_desasociar(Request $request){
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');
        $id_modalidad = $request->route('id_modalidad');

        $carrera = Carrera::find($id_carrera);
        $modalidad = Modalidad::find($id_modalidad);
        if($carrera and $modalidad){
            $todo = CarreraModalidad::where([
                'estado' => 1,
                'car_id' => $id_carrera,
                'mod_id' => $id_modalidad
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Carrera o el Tipo de Modalidad',
        ],404);
    }

    /**
    * @OA\Get(
    *     path="/carreras/{id_carrera}",
    *     tags={"Carreras"},
    *     summary="Mostrar carreras",
    *     description="Recupera la carrera de acuerdo al id",
    *     operationId="show",
    *     @OA\Parameter(ref="#/components/parameters/id_carrera"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo carreras.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function show(Request $request)
    {
        $id_carrera = $request->route('id_carrera');
        $todo = Carrera::with([
            'usuario',
            'departamento',
            'modalidades.modalidad',
            'modalidades'=>function($q){
                $q->where('estado',1);
            },
            'planesEstudio'=>function($q){
                $q->where('estado',1);
            },
            'plan_estudio',
        ])->where('car_id',$id_carrera)->first();
        return response()->json($todo,200);
    }

    /**
    * @OA\Put(
    *     path="/carreras/{id_carrera}",
    *     tags={"Carreras"},
    *     summary="Editar carreras",
    *     description="Edita la carrera de acuerdo al id",
    *     operationId="update",
    *     @OA\Parameter(ref="#/components/parameters/id_carrera"),
    *     @OA\RequestBody(
    *          description="Datos del carrera",
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve una sola carrera.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    *     @OA\Response(
    *         response="403",
    *         description="Error Validator."
    *     ),
    *     @OA\Response(
    *         response="default",
    *         description="Ha ocurrido un error."
    *     )
    * )
    */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'nombre_corto' => 'required',
            'titulo' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $nombre = $request->input('nombre');
        $nombre_corto = $request->input('nombre_corto');
        $titulo = $request->input('titulo');
        $todo = Carrera::find($id_carrera);
        if($todo){
            $todo->car_nombre = $nombre;
            $todo->car_nombre_corto = $nombre_corto;
            $todo->car_titulo = $titulo;
            $todo->save();
        } 
        return response()->json($todo,200);
    }

    public function seleccionar_plan(Request $request)
    {
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');
        $id_plan_estudio = $request->route('id_plan_estudio');

        $carrera = Carrera::find($id_carrera);
        $plan = PlanEstudio::where([
            'car_id' => $id_carrera,
            'pes_id' => $id_plan_estudio,
        ])->first();
        if($carrera and $plan){
            $carrera->pes_id = $id_plan_estudio;
            $carrera->save();
            return response()->json($carrera,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Carrera o el Plan de Estudio',
        ],404);
    }

    /**
    * @OA\Delete(
    *     path="/carreras/{id_carrera}",
    *     tags={"Carreras"},
    *     summary="Eliminar carrera",
    *     description="Elimina la carreras de acuerdo al id",
    *     operationId="destroy",
    *     @OA\Parameter(ref="#/components/parameters/id_carrera"),
    *     @OA\Response(
    *         response=200,
    *         description="Devuelve un solo una carrera.",
    *         @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(ref="#/components/schemas/Carrera")
    *          )
    *     ),
    * )
    */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');

        $todo = Carrera::find($id_carrera);
        if($todo){
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function plan_estudio(Request $request){
        $user = Auth::user();
        $id_carrera = $request->route('id_carrera');

        $id_plan_estudio = $request->input('id_plan_estudio');

        $todo = Carrera::find($id_carrera);
        if($todo){
            $todo->pes_id = $id_plan_estudio;
            $todo->save();
        }
        return response()->json($todo,200);
    }

}
