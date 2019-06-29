<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Materia;
use App\Models\MateriaCorrelatividad;
use App\Models\TipoMateriaLectivo;
use App\Models\TipoMateriaRegimen;
use App\Models\PlanEstudio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $page = $request->query('page',0);
        $length = $request->query('length',0);
        $registros = Materia::with([
            'usuario',
            'planEstudio',
            'tipoRegimen',
            'tipoLectivo',
            'correlatividades'=>function($q){
                $q->where('estado',1);
            },
            'correlatividades.correlatividad',
        ])->where([
            'estado' => 1 ,
        ]);

        $id_departamento = $request->query('id_departamento',0);
        $id_carrera = $request->query('id_carrera',0);
        $id_plan_estudio = $request->query('id_plan_estudio',0);
        $id_tipo_materia_regimen = $request->query('id_tipo_materia_regimen',0);
        $id_tipo_materia_lectivo = $request->query('id_tipo_materia_lectivo',0);
        $registros = $registros
            ->when($id_departamento>0,function($q)use($id_departamento){
                $carreras = Carrera::where([
                    'dep_id' => $id_departamento,
                    'estado' => 1,
                ])->pluck('car_id')->toArray();
                return $q->whereIn('car_id',$carreras);
            })
            ->when($id_carrera>0,function($q)use($id_carrera){
                return $q->whereHas('planEstudio',function($qt)use($id_carrera){
                    $qt->where('id_carrera',$id_carrera);
                });
            })
            ->when($id_plan_estudio>0,function($q)use($id_plan_estudio){
                return $q->where('id_plan_estudio',$id_plan_estudio);
            })
            ->when($id_tipo_materia_regimen>0,function($q)use($id_tipo_materia_regimen){
                return $q->where('tmr_id',$id_tipo_materia_regimen);
            })
            ->when($id_tipo_materia_lectivo>0,function($q)use($id_tipo_materia_lectivo){
                return $q->where('tml_id',$id_tipo_materia_lectivo);
            });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $page==0 ){
            $todo = $registros->orderBy('nombre','asc')
            ->get();
            return response()->json($todo,200);
        }
        
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query->where('mat_nombre','like','%'.$value.'%')
                    ->orWhere('mat_codigo','like','%'.$value.'%')
                    ->orWhere('mat_horas',$value);
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
        $total_count = $q->groupBy('pes_id')->count();
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

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'codigo' => 'required',
            'horas' => 'required | numeric',
            'id_plan_estudio' => 'required | integer',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $id_plan_estudio = $request->input('id_plan_estudio');
        $nombre = $request->input('nombre');
        $codigo = $request->input('codigo');
        $horas = $request->input('horas',0);
        $id_tipo_materia_lectivo = $request->input('id_tipo_materia_lectivo',1);
        $id_tipo_materia_regimen = $request->input('id_tipo_materia_regimen',1);

        $todo = new Materia;
        $todo->mat_nombre = $nombre;
        $todo->mat_codigo = $codigo;
        $todo->mat_horas = $horas;
        $todo->tml_id = $id_tipo_materia_lectivo;
        $todo->tmr_id = $id_tipo_materia_regimen;
        $todo->pes_id = $id_plan_estudio;
        $todo->usu_id = $user->id;
        $todo->save();

        $plan = PlanEstudio::find($id_plan_estudio);
        if($plan){
            $plan->pes_horas = $plan->pes_horas + $horas;
            $plan->save();
        }

        return response()->json($todo,200);
    }

    public function correlatividad_asociar(Request $request){
        $user = Auth::user();
        $id_materia = $request->route('id_materia');
        $correlatividad_id_materia = $request->route('correlatividad_id_materia');

        $materia = Materia::find($id_materia);
        $correlatividad = Materia::find($correlatividad_id_materia);
        if($materia and $correlatividad){
            $id_tipo_correlatividad = $request->input('id_tipo_correlatividad',1);
            $todo = MateriaCorrelatividad::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mat_id_materia' => $correlatividad_id_materia,
            ])->first();
            if($todo){
                $todo->tco_id = $id_tipo_correlatividad;
                $todo->usu_id = $user->id;
                $todo->save();
            } else {
                $todo = new MateriaCorrelatividad;
                $todo->mat_id = $id_materia;
                $todo->mat_id_materia = $correlatividad_id_materia;
                $todo->tco_id = $id_tipo_correlatividad;
                $todo->usu_id = $user->id;
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o su Correlatividad',
        ],401);
    }

    public function correlatividad_desasociar(Request $request){
        $user = Auth::user();
        $id_materia = $request->route('id_materia');
        $correlatividad_id_materia = $request->route('correlatividad_id_materia');

        $materia = Materia::find($id_materia);
        $correlatividad = Materia::find($correlatividad_id_materia);
        if($materia and $correlatividad){
            $todo = MateriaCorrelatividad::where([
                'estado' => 1,
                'mat_id' => $id_materia,
                'mat_id_materia' => $correlatividad_id_materia,
            ])->first();
            if($todo){
                $todo->estado = 0;
                $todo->save();
            }
            return response()->json($todo,200);
        }
        return response()->json([
            'error'=>'No se han encontrado la Materia o su Correlatividad',
        ],401);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TipoUsuario  $tipoUsuario
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_materia = $request->route('id_materia');
        $todo = Materia::with([
            'usuario',
            'planEstudio',
            'tipoRegimen',
            'tipoLectivo',
            'correlatividades'=>function($q){
                $q->where('estado',1);
            },
        ])->find($id_materia);
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
        $id_materia = $request->route('id_materia');
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
            'codigo' => 'required',
            'horas' => 'required | numeric',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre = $request->input('nombre');
        $codigo = $request->input('codigo');
        $horas = $request->input('horas',0);
        $id_tipo_materia_lectivo = $request->input('id_tipo_materia_lectivo',1);
        $id_tipo_materia_regimen = $request->input('id_tipo_materia_regimen',1);

        $todo = Materia::find($id_materia);
        if($todo){
            $plan = PlanEstudio::find($todo->id_plan_estudio);
            if($plan){
                $plan->pes_horas = $plan->pes_horas - $todo->mat_horas + $horas;
                $plan->save();
            }
            $todo->mat_nombre = $nombre;
            $todo->mat_codigo = $codigo;
            $todo->mat_horas = $horas;
            $todo->tml_id = $id_tipo_materia_lectivo;
            $todo->tmr_id = $id_tipo_materia_regimen;
            $todo->save();
        } 
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
        $id_materia = $request->route('id_materia');

        $todo = Materia::find($id_materia);
        if($todo){
            $plan = PlanEstudio::find($todo->id_plan_estudio);
            if($plan){
                $plan->pes_horas = $plan->pes_horas - $todo->mat_horas + $horas;
                $plan->save();
            }
            $todo->estado = 0;
            $todo->save();
        }
        return response()->json($todo,200);
    }

    public function tipos_regimen(Request $request){
        $todo = TipoMateriaRegimen::where('estado',1)->get();
        return response()->json($todo,200);
    }

    public function tipos_lectivo(Request $request){
        $todo = TipoMateriaLectivo::where('estado',1)->get();
        return response()->json($todo,200);
    }
}
