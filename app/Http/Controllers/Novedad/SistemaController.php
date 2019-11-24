<?php

namespace App\Http\Controllers\Novedad;

use App\User;
use App\Models\Novedad\Sistema;
use App\Models\Novedad\Usuario as NovedadUsuario;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

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
        $id_usuario = $request->query('id_usuario',0);
        
        $registros = Sistema::where([
            'sed_id' => $id_sede,
            'estado' => 1,
        ])
        ->when($id_usuario>0,function($q)use($id_usuario){
            $q
            ->where('mostrar',1)
            ->whereHas('usuarios',function($qt)use($id_usuario){
                $qt->where('id_usuario',$id_usuario);
            });
        });

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $length==0 ){
            $todo = $registros->orderBy('created_at','desc')
            ->get();
            return response()->json($todo);
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

        $no_visto = [];
        if($id_usuario>0){
            $no_visto = NovedadUsuario::whereHas('novedad',function($q)use($id_usuario){
                    $q->where('mostrar',1);
                })
                ->where('id_usuario',$id_usuario)
                ->whereNull('visto')
                ->get();
        }

        return response()->json([
            'total_count'=>intval($total_count),
            'items'=>$registros,
            'no_visto' => $no_visto,
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

        $todo = new Sistema;
        $todo->titulo = $titulo;
        $todo->descripcion = $descripcion;
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

        return response()->json($todo);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Novedad\Sistema  $sistema
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id_usuario = $request->query('id_usuario',0);
        $todo = Sistema::find($request->sistema);
        $todo->cuerpo = $todo->nsi_cuerpo;

        if($id_usuario>0){
            $usuario = NovedadUsuario::where('id_usuario',$id_usuario)
            ->where('estado',1)
            ->where('id_novedad_sistema',$todo->id)
            ->whereNull('visto')
            ->first();
            if($usuario){
                $usuario->visto = Carbon::now();
                $usuario->save();
            }
        }
        return response()->json($todo);
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
        
        $todo = Sistema::find($request->sistema);
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
        return response()->json($todo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Novedad\Sistema  $sistema
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $todo = Sistema::find($request->sistema);
        $todo->estado = 0;
        $todo->save();
        $usuarios = NovedadUsuario::where('id_novedad_sistema',$todo->id)->get();
        foreach ($usuarios as $usuario) {
            $novedad = NovedadUsuario::find($usuario->id);
            $novedad->estado = 0;
            $novedad->save();
        }
        return response()->json($todo);
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
        return response()->json($todo);
    }

    public function usuarios(Request $request){
        $id_novedad_sistema = $request->route('id_novedad_sistema');
        $usuarios = NovedadUsuario::with('usuario')
            ->where('id_novedad_sistema',$id_novedad_sistema)->get();
        return response()->json($usuarios);
    }
}
