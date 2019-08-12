<?php

namespace App\Http\Controllers\Extra;

use App\Models\Extra\ReporteJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ReporteJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);
        $registros = ReporteJob::with('usuario')
        ->where([
            'sed_id' => $id_sede,
            'estado' => 1,
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
                  $query->where('nombre','like','%'.$value.'%');
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

    public function terminados(Request $request){
        $id_sede = $request->route('id_sede');
        $todo = ReporteJob::whereNull('terminado')->where('estado',1)->get();
        $registros = ReporteJob::whereNull('terminado')->where('estado',1)->where('id_sede',$id_sede)->get();
        return response()->json([
            'total_count' => count($todo),
            'items' => $registros,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json([
            'error' => 'No implementado',
        ],403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Extra\ReporteJob  $reporteJob
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $reporteJob = ReporteJob::find($request->reporteJob);
        return response()->download(storage_path("app/{$reporteJob->rjo_dir}"),$reporteJob->nombre.'.zip');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Extra\ReporteJob  $reporteJob
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        return response()->json([
            'error' => 'No implementado',
        ],403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Extra\ReporteJob  $reporteJob
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $reporteJob = ReporteJob::find($request->reporteJob);
        Storage::delete($reporteJob->rjo_dir);
        $reporteJob->estado = 0;
        $reporteJob->save();
        return response()->json($reporteJob,200);
    }
}
