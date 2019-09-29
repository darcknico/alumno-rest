<?php

namespace App\Http\Controllers\Academico;

use App\Models\Academico\Aula;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Validator;

class AulaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_sede = $request->route('id_sede',0);
        $search = $request->query('search','');
        $sort = $request->query('sort','');
        $order = $request->query('order','');
        $start = $request->query('start',0);
        $length = $request->query('length',0);

        $registros = Aula::where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ]);
        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('numero','asc')
            ->get();
            return response()->json($todo,200);
        }
        
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
              if(strlen($value)>0){
                $registros = $registros->where(function($query) use  ($value) {
                  $query
                    ->where('nombre','like','%'.$value.'%')
                    ->orWhere('numero',$value);
                });
              }
            }
        }
        if(strlen($sort)>0){
        $registros = $registros->orderBy($sort,$order);
        } else {
        $registros = $registros->orderBy('cuit','desc');
        }
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_sede = $request->route('id_sede');
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
          'numero' => 'required | integer',
          'capacidad' => 'integer'
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $numero = $request->input('numero');
        $nombre = $request->input('nombre');
        $capacidad = $request->input('capacidad');

        $aula = new Aula;
        $aula->id_sede = $id_sede;
        $aula->numero = $numero;
        $aula->nombre = $nombre;
        $aula->capacidad = $capacidad;
        $aula->save();

        return response()->json($aula,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\Aula  $aula
     * @return \Illuminate\Http\Response
     */
    public function show(Aula $aula)
    {
        return response()->json($aula,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\Aula  $aula
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
          'numero' => 'required | integer',
          'capacidad' => 'integer'
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $numero = $request->input('numero');
        $nombre = $request->input('nombre');
        $capacidad = $request->input('capacidad');

        $aula = Aula::find($request->aula);
        $aula->numero = $numero;
        $aula->nombre = $nombre;
        $aula->capacidad = $capacidad;
        $aula->save();
        return response()->json($aula,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\Aula  $aula
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $aula = Aula::find($request->aula);
        $aula->estado = 0;
        $aula->save();
        return response()->json($aula,200);
    }
}
