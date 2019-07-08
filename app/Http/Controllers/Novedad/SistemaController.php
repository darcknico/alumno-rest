<?php

namespace App\Http\Controllers\Novedad;

use App\User;
use App\Models\Novedad\Sistema;
use App\Models\Novedad\Usuario as NovedadUsuario;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use voku\CssToInlineStyles\CssToInlineStyles;

class SistemaController extends Controller
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
        
        $registros = Sistema::where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ]);

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo,200);
        }
        /*
        $values = explode(" ", $search);
        if(count($values)>0){
            foreach ($values as $key => $value) {
                if(strlen($value)>0){
                    $registros = $registros->where(function($query) use  ($value) {
                        $query->where('titulo','like','%'.$value.'%');
                        });
                    });
                }
            }
        }
        */

        $sql = $registros->toSql();
        $q = clone($registros->getQuery());
        $total_count = $q->groupBy('estado')->count();

        if(strlen($sort)>0){
            $registros = $registros->orderBy($sort,$order);
        } else {
            $registros = $registros->orderBy('created_at','desc');
        }
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
        $cssToInlineStyles = new CssToInlineStyles();
        $cssToInlineStyles->setUseInlineStylesBlock(true);
        $user = Auth::user();
        $id_sede = $request->route('id_sede');

        $validator = Validator::make($request->all(),[
            'titulo' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $titulo = $request->input('titulo');
        $descripcion = $request->input('descripcion');
        $cuerpo = $request->input('cuerpo');
        $cssToInlineStyles->setHTML($cuerpo);
        $todo = new Sistema;
        $todo->titulo = $titulo;
        $todo->descripcion = $descripcion;
        $todo->nsi_cuerpo = $cssToInlineStyles->convert();
        $todo->id_sede = $id_sede;
        $todo->id_usuario = $user->id;
        $todo->save();

        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            $novedad = new NovedadUsuario;
            $novedad->id_usuario = $usuario->id;
            $novedad->id_novedad_sistema = $todo->id;
            $novedad->save();
        }

        return response()->json($todo,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Novedad\Sistema  $sistema
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $todo = Sistema::find($request->novedadSistema);
        $todo->cuerpo = $todo->nsi_cuerpo;
        return response()->json($todo,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Novedad\Sistema  $sistema
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $cssToInlineStyles = new CssToInlineStyles();
        $cssToInlineStyles->setUseInlineStylesBlock(true);
        $validator = Validator::make($request->all(),[
            'titulo' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $titulo = $request->input('titulo');
        $descripcion = $request->input('descripcion');
        $cuerpo = $request->input('cuerpo');
        $cssToInlineStyles->setHTML($cuerpo);
        
        $todo = Sistema::find($request->novedadSistema);
        $todo->titulo = $titulo;
        $todo->descripcion = $descripcion;
        $todo->nsi_cuerpo = $cssToInlineStyles->convert();
        $todo->save();

        $usuarios = User::all();
        foreach ($usuarios as $usuario) {
            $novedad = NovedadUsuario::where('id_usuario',$usuario->id)->where('id_novedad_sistema',$todo->id)->first();
            if($novedad){
                $novedad->visto = null;
            } else {
                $novedad = new NovedadUsuario;
                $novedad->id_usuario = $usuario->id;
                $novedad->id_novedad_sistema = $todo->id;
            }
            $novedad->save();
        }
        return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Novedad\Sistema  $sistema
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $todo = Sistema::find($request->novedadSistema);
        $todo->estado = 0;
        $todo->save();
        $usuarios = NovedadUsuario::where('id_novedad_sistema',$todo->id)->get();
        foreach ($usuarios as $usuario) {
            $novedad = NovedadUsuario::find($usuario->id);
            $novedad->estado = 0;
            $novedad->save();
        }
        return response()->json($todo,200);
    }

    public function mostrar(Request $request){
        $id_novedad_sistema = $request->route('id_novedad_sistema');

        $validator = Validator::make($request->all(),[
            'mostrar' => 'required | boolean',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],403);
        }
        $todo = Sistema::find($id_novedad_sistema);
        $todo->mostrar = $request->input('mostrar');
        $todo->save();
        return response()->json($todo,200);
    }

    public function usuarios(Request $request){
        $id_novedad_sistema = $request->route('id_novedad_sistema');
        $usuarios = NovedadUsuario::with('usuario')
            ->where('id_novedad_sistema',$id_novedad_sistema)->get();
        return response()->json($usuarios,200);
    }
}
