<?php

namespace App\Http\Controllers\Mesa;

use App\User;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\PlanEstudio;
use App\Models\Carrera;
use App\Models\CarreraModalidad;
use App\Models\Materia;
use App\Models\Inscripcion;
use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Models\Asistencia;
use App\Models\Sede;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use Carbon\Carbon;

class MesaExamenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $registros = MesaExamen::with('sede','usuario')
            ->where([
            'estado' => 1,
            'sed_id' => $id_sede,
            ]);

        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('fecha_inicio','desc')
            ->get();
            return response()->json($todo,200);
        }

        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('numero', $value);
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
        $total_count = $q->groupBy('sed_id')->count();
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

    /**
     * Store
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin',$fecha_inicio);
        if(empty($fecha_fin)){
            $fecha_fin = $fecha_inicio;
        }
        $nombre = $request->input('nombre');

        $sede = Sede::find($id_sede);
        $numero = $sede->mesa_numero + 1;
        $todo = new MesaExamen;
        $todo->numero = $numero;
        $todo->nombre = $nombre;
        $todo->fecha_inicio = $fecha_inicio;
        $todo->fecha_fin = $fecha_fin;
        $todo->id_sede = $id_sede;
        $todo->usu_id= $user->id;
        $todo->save();
        $sede->mesa_numero = $numero;
        $sede->save();

        return response()->json($todo,200);
    }

    public function materia_asociar(Request $request){
        $user = Auth::user();
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_materia = $request->route('id_materia');

        $validator = Validator::make($request->all(),[
            'fecha' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }

        $fecha = $request->input('fecha');
        $ubicacion = $request->input('ubicacion',null);
        $mesa_examen = MesaExamen::find($id_mesa_examen);
        $materia = Materia::find($id_materia);
        if($mesa_examen and $materia){
            $todo = MesaExamenMateria::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mes_id' => $id_mesa_examen,
            ])->first();
            if($todo){
                response()->json([
                    'error'=>'La materia ya cuenta con mesa de examen.',
                ],404);
            } else {
                $todo = new MesaExamenMateria;
                $todo->id_mesa_examen = $id_mesa_examen;
                $todo->id_carrera = $materia->planEstudio->id_carrera;
                $todo->id_materia = $id_materia;
                $todo->usu_id = $user->id;
                $todo->fecha = $fecha;
                $todo->ubicacion = $ubicacion;
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o la Mesa de examen.',
        ],404);
    }

    public function materia_desasociar(Request $request){
        $user = Auth::user();
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_materia = $request->route('id_materia');

        $mesa_examen = MesaExamen::find($id_mesa_examen);
        $materia = Materia::find($id_materia);
        if($mesa_examen and $materia){
            $todo = MesaExamenMateria::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mes_id' => $id_mesa_examen,
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->usu_id_baja = $user->id;
                $todo->deleted_at = Carbon::now();
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o la Mesa de examen.',
        ],404);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_mesa_examen = $request->route('id_mesa_examen');
        $todo = MesaExamen::with([
            'usuario',
            'sede',
        ])->find($id_mesa_examen);
        return response()->json($todo,200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function edit(TipoUsuario $tipoUsuario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $id_mesa_examen = $request->route('id_mesa_examen');
        $validator = Validator::make($request->all(),[
            'fecha_fin' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],404);
        }
        $fecha_fin = $request->input('fecha_fin');
        $nombre = $request->input('nombre');

        $todo = MesaExamen::find($id_mesa_examen);
        $todo->nombre = $nombre;
        $todo->fecha_fin = $fecha_fin;
        $todo->save();
        return response()->json($todo,200);
    }

    public function materias(Request $request){
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_carrera = $request->query('id_carrera',0);

        $todo = MesaExamenMateria::with('materia.planEstudio','carrera','usuario')
        ->when($id_carrera>0,function($q)use($id_carrera){
          return $q->where('id_carrera',$id_carrera);
        })
        ->where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
        ])->orderBy('fecha','asc')->get();
        return response()->json($todo,200);
    }

    public function materias_disponibles_comision(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');
        $fecha = Carbon::now();
        $year = $fecha->year - 1;
        $materias = Comision::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ])->where('anio','>=',$year)
        ->pluck('mat_id')->toArray();
        $mesas = MesaExamenMateria::where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
        ])->pluck('mat_id')->toArray();
        $todo = Materia::with('planEstudio.carrera')
        ->where([
            'estado' => 1,
        ])
        ->whereIn('mat_id',$materias)
        ->whereNotIn('mat_id',$mesas)
        ->orderBy('codigo','desc')
        ->get();
        return response()->json($todo,200);
    }

    public function materias_disponibles(Request $request){
        $id_sede = $request->route('id_sede');
        $id_mesa_examen = $request->route('id_mesa_examen');
        $id_carrera = $request->query('id_carrera',0);

        $mesas = MesaExamenMateria::where([
            'estado' => 1,
            'mes_id' => $id_mesa_examen,
        ])->pluck('mat_id')->toArray();
        $carreras = Carrera::where('estado',1)->pluck('car_id')->toArray();
        $planes_estudio = PlanEstudio::whereIn('car_id',$carreras)
            ->where('estado',1)
            ->when($id_carrera>0,function($q)use($id_carrera){
              return $q->where('car_id',$id_carrera);
            })->pluck('pes_id')->toArray();
        $todo = Materia::with('planEstudio.carrera')
        ->where([
            'estado' => 1,
        ])
        ->whereIn('pes_id',$planes_estudio)
        ->whereNotIn('mat_id',$mesas)
        ->orderBy('codigo','desc')
        ->get();
        return response()->json($todo,200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $id_mesa_examen = $request->route('id_mesa_examen');

        $todo = MesaExamen::find($id_mesa_examen);
        if($todo){
            $todo->estado = 0;
            $todo->deleted_at = Carbon::now();
            $todo->usu_id_baja = $user->id;
            $todo->save();
        }
        return response()->json($todo,200);
    }

}
