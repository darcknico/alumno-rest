<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\MateriaCorrelatividad;
use App\Models\TipoMateriaLectivo;
use App\Models\TipoMateriaRegimen;
use App\Models\PlanEstudio;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use \DB;
use Carbon\Carbon;

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
        $start = $request->query('start',0);
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
                $q->whereHas('planEstudio',function($qt)use($id_departamento){
                    $qt->where('estado',1)
                        ->whereHas('carrera',function($qtr)use($id_departamento){
                            $qtr->where('estado',1)
                                ->where('id_departamento',$id_departamento);
                        });
                });
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

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 ){
            $todo = $registros->orderBy('codigo','asc')
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
        $total_count = count($q->get());
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
            'correlatividades.correlatividad'
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
                $plan->pes_horas = $plan->pes_horas - $todo->mat_horas;
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

    public function estadisticas_historico(Request $request){
    	$id_materia = $request->route('id_materia');

    	$id_sede = $request->query('id_sede',null);

    	$examenes = "
    		SELECT
    		COUNT(cex.cex_id) as total
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_comision_examen cex on com.com_id = cex.com_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.estado = 1 AND
    			cex.estado = 1
    		GROUP BY com.mat_id
    	";
    	$examenes = DB::select($examenes, [
            $id_sede,
            $id_materia,
            ]
        );

    	$examinados = "
    		SELECT
    		COUNT(cea.cae_nota) as total,
    		AVG(cea.cae_nota) as promedio,
    		SUM(IF( cea.cae_nota > 5,1,0 )) as aprobados,
    		SUM(IF( cea.cae_nota <= 5,1,0 )) as reprobados,
    		SUM(IF( cea.taa_id = 2,1,0 )) as ausentes,
    		SUM(IF( cea.taa_id = 4,1,0 )) as presentes,
    		SUM(IF( cea.taa_id = 5,1,0 )) as justificados
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_comision_examen cex on com.com_id = cex.com_id
    			RIGHT JOIN tbl_comision_examen_alumno cea on cex.cex_id = cea.cex_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.estado = 1 AND
    			cex.estado = 1 AND
    			cea.estado = 1
    		GROUP BY com.mat_id
    	";
    	$examinados = DB::select($examinados, [
            $id_sede,
            $id_materia,
            ]
        );

        $asistencias = "
    		SELECT
    		COUNT(asi.asi_id) as total
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_asistencias asi on com.com_id = asi.com_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.estado = 1 AND
    			asi.estado = 1
    		GROUP BY com.mat_id
    	";
    	$asistencias = DB::select($asistencias, [
            $id_sede,
            $id_materia,
            ]
        );

    	$asistidos = "
    		SELECT
    		COUNT(aal.taa_id) as total,
    		SUM(IF( aal.taa_id = 2,1,0 )) as ausentes,
    		SUM(IF( aal.taa_id = 4,1,0 )) as presentes,
    		SUM(IF( aal.taa_id = 5,1,0 )) as justificados
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_asistencias asi on com.com_id = asi.com_id
    			RIGHT JOIN tbl_asistencia_alumno aal on asi.asi_id = aal.asi_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.estado = 1 AND
    			asi.estado = 1 AND
    			aal.estado = 1
    		GROUP BY com.mat_id
    	";
    	$asistidos = DB::select($asistidos, [
            $id_sede,
            $id_materia,
            ]
        );

        $mesas = "
    		SELECT
    			COUNT(mma.mma_id) as total,
    			COUNT(mma.mma_fecha_cierre) as cerrados,
    			SUM(mma.mma_alumnos_cantidad) as alumnos,
    			SUM(mma.mma_alumnos_cantidad_presente) as presentes,
    			SUM(mma.mma_alumnos_cantidad_aprobado) as aprobados,
    			SUM(mma.mma_alumnos_cantidad_no_aprobado) as no_aprobados
    		FROM
    			tbl_mesas_examen mes
    			RIGHT JOIN tbl_mesa_materia mma on mes.mes_id = mma.mes_id
    		WHERE
    			mes.sed_id = ? AND
    			mma.mat_id = ? AND
    			mes.estado = 1 AND
    			mma.estado = 1
    		GROUP BY mma.mat_id
    	";
    	$mesas = DB::select($mesas, [
            $id_sede,
            $id_materia,
            ]
        );

        return response()->json([
        	'examenes' => $examenes[0]??null,
        	'examinados' => $examinados[0]??null,
        	'asistencias' => $asistencias[0]??null,
        	'asistidos' => $asistidos[0]??null,
        	'mesas' => $mesas[0]??null,
        ]);
    }

    public function estadisticas_anual(Request $request){
    	$id_materia = $request->route('id_materia');

    	$id_sede = $request->query('id_sede',null);
    	$anio = $request->query('anio',null);
    	if(is_null($anio)){
    		$anio = Carbon::now()->year;
    	}
    	$examenes = "
    		SELECT
    		COUNT(cex.cex_id) as total
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_comision_examen cex on com.com_id = cex.com_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.com_anio = ? AND
    			com.estado = 1 AND
    			cex.estado = 1
    		GROUP BY com.mat_id
    	";
    	$examenes = DB::select($examenes, [
            $id_sede,
            $id_materia,
            $anio,
            ]
        );

    	$examinados = "
    		SELECT
    		COUNT(cea.cae_nota) as total,
    		AVG(cea.cae_nota) as promedio,
    		SUM(IF( cea.cae_nota > 5,1,0 )) as aprobados,
    		SUM(IF( cea.cae_nota <= 5,1,0 )) as reprobados,
    		SUM(IF( cea.taa_id = 2,1,0 )) as ausentes,
    		SUM(IF( cea.taa_id = 4,1,0 )) as presentes,
    		SUM(IF( cea.taa_id = 5,1,0 )) as justificados
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_comision_examen cex on com.com_id = cex.com_id
    			RIGHT JOIN tbl_comision_examen_alumno cea on cex.cex_id = cea.cex_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.com_anio = ? AND
    			com.estado = 1 AND
    			cex.estado = 1 AND
    			cea.estado = 1
    		GROUP BY com.mat_id
    	";
    	$examinados = DB::select($examinados, [
            $id_sede,
            $id_materia,
            $anio,
            ]
        );

        $asistencias = "
    		SELECT
    		COUNT(asi.asi_id) as total
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_asistencias asi on com.com_id = asi.com_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.com_anio = ? AND
    			com.estado = 1 AND
    			asi.estado = 1
    		GROUP BY com.mat_id
    	";
    	$asistencias = DB::select($asistencias, [
            $id_sede,
            $id_materia,
            $anio,
            ]
        );

    	$asistidos = "
    		SELECT
    		COUNT(aal.taa_id) as total,
    		SUM(IF( aal.taa_id = 2,1,0 )) as ausentes,
    		SUM(IF( aal.taa_id = 4,1,0 )) as presentes,
    		SUM(IF( aal.taa_id = 5,1,0 )) as justificados
    		FROM
    			tbl_comisiones com
    			RIGHT JOIN tbl_asistencias asi on com.com_id = asi.com_id
    			RIGHT JOIN tbl_asistencia_alumno aal on asi.asi_id = aal.asi_id
    		WHERE
    			com.sed_id = ? AND
    			com.mat_id = ? AND
    			com.com_anio = ? AND
    			com.estado = 1 AND
    			asi.estado = 1 AND
    			aal.estado = 1
    		GROUP BY com.mat_id
    	";
    	$asistidos = DB::select($asistidos, [
            $id_sede,
            $id_materia,
            $anio,
            ]
        );

        $mesas = "
    		SELECT
    			COUNT(mma.mma_id) as total,
    			COUNT(mma.mma_fecha_cierre) as cerrados,
    			SUM(mma.mma_alumnos_cantidad) as alumnos,
    			SUM(mma.mma_alumnos_cantidad_presente) as presentes,
    			SUM(mma.mma_alumnos_cantidad_aprobado) as aprobados,
    			SUM(mma.mma_alumnos_cantidad_no_aprobado) as no_aprobados
    		FROM
    			tbl_mesas_examen mes
    			RIGHT JOIN tbl_mesa_materia mma on mes.mes_id = mma.mes_id
    		WHERE
    			mes.sed_id = ? AND
    			mma.mat_id = ? AND
    			YEAR(mma.mma_fecha) = ? AND
    			mes.estado = 1 AND
    			mma.estado = 1
    		GROUP BY mma.mat_id
    	";
    	$mesas = DB::select($mesas, [
            $id_sede,
            $id_materia,
            $anio,
            ]
        );

        return response()->json([
        	'examenes' => $examenes[0]??null,
        	'examinados' => $examinados[0]??null,
        	'asistencias' => $asistencias[0]??null,
        	'asistidos' => $asistidos[0]??null,
        	'mesas' => $mesas[0]??null,
        ]);
    }
}
