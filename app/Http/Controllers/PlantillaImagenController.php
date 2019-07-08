<?php

namespace App\Http\Controllers;

use App\Models\PlantillaImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

class PlantillaImagenController extends Controller
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
        $page = $request->query('page',0);
        $length = $request->query('length',0);

        $registros = PlantillaImagen::where([
            'estado' => 1,
            'sed_id' => $id_sede,
        ]);
        if(strlen($sort)==0 and strlen($order)==0 and $page==0 ){
            $todo = $registros
              ->orderBy('created_at','desc')
              ->get();
            return response()->json($todo,200);
        }

        
        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('created_at','desc');
        }
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
        $filename = null;
        $dir = null;
        $plantillaImagen = null;
        if($request->hasFile('archivo')){
            $archivo = $request->file('archivo');
            $filename = $archivo->getClientOriginalName();
            $dir = $archivo->store('plantillas/imagenes');

            $plantillaImagen = new PlantillaImagen;
            $plantillaImagen->id_sede = $id_sede;
            $plantillaImagen->nombre = $filename;
            $plantillaImagen->pim_dir = $dir;
            $plantillaImagen->id_usuario = $user->id;
            $plantillaImagen->save();
        }
        return response()->json($plantillaImagen,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlantillaImagen  $plantillaImagen
     * @return \Illuminate\Http\Response
     */
    public function show(PlantillaImagen $plantillaImagen)
    {
        return response()->json($plantillaImagen,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PlantillaImagen  $plantillaImagen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PlantillaImagen $plantillaImagen)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],401);
        }
        $nombre =$request->input('nombre');
        $plantillaImagen->nombre = $nombre;
        $plantillaImagen->save();

        return response()->json($plantillaImagen,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlantillaImagen  $plantillaImagen
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = $request->imagene;
        $plantillaImagen = PlantillaImagen::find($id);
        $plantillaImagen->estado = 0;
        Storage::delete($plantillaImagen->pim_dir);
        $plantillaImagen->save();

        return response()->json($plantillaImagen,200);
    }
}
