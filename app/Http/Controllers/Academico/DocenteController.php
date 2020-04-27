<?php

namespace App\Http\Controllers\Academico;

use App\User;
use App\Models\Sede;
use App\Models\UsuarioSede;
use App\Models\Academico\Docente;
use App\Models\Academico\DocenteContrato;
use App\Models\Tipos\TipoContrato;
use App\Filters\DocenteFilter;
use App\Exports\DocenteExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;

class DocenteController extends Controller
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

        $registros = Docente::whereHas('usuario',function($q){
            $q->where('id_tipo_usuario',8);
        });

        $registros = DocenteFilter::index($request,$registros);

        if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
            $todo = $registros->orderBy('cuit','desc')
            ->get();
            return response()->json($todo,200);
        }
        $q = clone($registros->getQuery());
        $total_count = $q->count();

        $registros->select('tbl_docentes.*','tbl_usuarios.usu_apellido as apellido','tbl_usuarios.usu_nombre as nombre')
        ->rightJoin('tbl_usuarios','tbl_usuarios.usu_id','=','tbl_docentes.usu_id');

        if(strlen($sort)>0){
          $registros = $registros->orderBy($sort,$order);
        } else {
          $registros = $registros->orderBy('cuit','desc');
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
        $user = Auth::user();
        $validator = Validator::make($request->all(),[
          'email' => 'required | email',
          'nombre' => 'required',
          'fecha_nacimiento' => 'nullable | date'
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $email = $request->input('email');
        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $fecha_nacimiento = $request->input('fecha_nacimiento');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $direccion = $request->input('direccion');
        $direccion_numero = $request->input('direccion_numero');
        $direccion_piso = $request->input('direccion_piso');
        $documento = $request->input('documento');
        $id_tipo_documento = $request->input('id_tipo_documento');

        $titulo = $request->input('titulo');
        $cuit = $request->input('cuit');
        $observaciones = $request->input('observaciones');

        $docente = null;
        try {
            $todo = new User;
            $todo->email = $email;
            $todo->nombre = $nombre;
            $todo->apellido = $apellido;
            $todo->fecha_nacimiento = $fecha_nacimiento;
            $todo->telefono = $telefono;
            $todo->celular = $celular;
            $todo->direccion = $direccion;
            $todo->direccion_numero = $direccion_numero;
            $todo->direccion_piso = $direccion_piso;
            $todo->documento = $documento;
            $todo->id_tipo_documento = $id_tipo_documento;
            $todo->id_tipo_usuario = 8;
            $todo->usu_password =bcrypt('123456');
            $todo->estado = false;
            $todo->save();

            $sedes = $request->input('sedes',[]);
            foreach ($sedes as $sede) {
                $asociacion = new UsuarioSede;
                $asociacion->usu_id = $todo->id;
                $asociacion->sed_id = $sede['id_sede'];
                $asociacion->usu_id_usuario = $user->id;
                $asociacion->save();
            }

            $contratos = $request->input('contratos',[]);
            foreach ($contratos as $contrato) {
                $cont = new DocenteContrato;
                $cont->id_usuario = $todo->id;
                $cont->id_tipo_contrato = $contrato['id_tipo_contrato'];
                $cont->save();
            }

            $docente = new Docente;
            $docente->id_usuario = $todo->id;
            $docente->titulo = $titulo;
            $docente->cuit = $cuit;
            $docente->observaciones = $observaciones;
            $docente->save();
        } catch(\Illuminate\Database\QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == '1062'){
                return response()->json(['error'=>'Email Duplicado'],403);
            } else {
                return response()->json($e,403);
            }
        } catch (\Exception $e) {
            return response()->json($e,403);
        }
        return response()->json($docente,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Academico\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function show(Docente $docente)
    {
        return $docente;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Academico\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Docente $docente)
    {
        $validator = Validator::make($request->all(),[
          'nombre' => 'required',
          'fecha_nacimiento' => 'nullable | date'
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
        }
        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $fecha_nacimiento = $request->input('fecha_nacimiento');
        $telefono = $request->input('telefono');
        $celular = $request->input('celular');
        $direccion = $request->input('direccion');
        $direccion_numero = $request->input('direccion_numero');
        $direccion_piso = $request->input('direccion_piso');
        $documento = $request->input('documento');
        $id_tipo_documento = $request->input('id_tipo_documento');

        $titulo = $request->input('titulo');
        $cuit = $request->input('cuit');
        $observaciones = $request->input('observaciones');

        try {
            $user = User::find($docente->id_usuario);
            $user->nombre = $nombre;
            $user->apellido = $apellido;
            $user->fecha_nacimiento = $fecha_nacimiento;
            $user->telefono = $telefono;
            $user->celular = $celular;
            $user->direccion = $direccion;
            $user->direccion_numero = $direccion_numero;
            $user->direccion_piso = $direccion_piso;
            $user->documento = $documento;
            $user->id_tipo_documento = $id_tipo_documento;
            $user->save();

            $docente->titulo = $titulo;
            $docente->cuit = $cuit;
            $docente->observaciones = $observaciones;
            $docente->save();
        } catch(\Illuminate\Database\QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == '1062'){
                return response()->json(['error'=>'Email Duplicado'],403);
            }
        }
        return response()->json($docente,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Academico\Docente  $docente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Docente $docente)
    {
        $usuario = User::find($docente->id_usuario);
        $usuario->estado = 0;
        $usuario->save();
        return response()->json($docente,200);
    }

    public function contrato_seleccionar(Request $request){
      $user = Auth::user();
      $id_tipo_contrato = $request->route('id_tipo_contrato');
      $id_usuario = $request->route('id_usuario');

      $docente = Docente::find($id_usuario);
      $contrato = TipoContrato::find($id_tipo_contrato);
      if($docente and $contrato){
        $todo = DocenteContrato::where([
          'estado' => 1,
          'tco_id' => $id_tipo_contrato,
          'usu_id' => $id_usuario,
        ])->first();
        if($todo){
          $todo->save();
        } else {
            $todo = new DocenteContrato;
            $todo->id_usuario = $id_usuario;
            $todo->id_tipo_contrato = $id_tipo_contrato;
            $todo->save();
        }
        return response()->json($todo,200);
      }
      return response()->json([
          'error'=>'No se han encontrado al Docente o tipo de contrato',
      ],403);
    }

    public function contrato_desasociar(Request $request){
      $user = Auth::user();
      $id_tipo_contrato = $request->route('id_tipo_contrato');
      $id_usuario = $request->route('id_usuario');

      $docente = Docente::find($id_usuario);
      $contrato = TipoContrato::find($id_tipo_contrato);
      if($docente and $contrato){
          $todo = DocenteContrato::where([
              'estado' => 1,
              'tco_id' => $id_tipo_contrato,
              'usu_id' => $id_usuario,
          ])->first();
          if($todo){
              $todo->estado = 0;
              $todo->save();
          }
          return response()->json($todo,200);
      }
      return response()->json([
          'error'=>'No se han encontrado al Docente o tipo de contrato',
      ],403);
    }

    public function exportar(Request $request){

        $fecha = Carbon::now()->format('d.m.Y');

        return (new DocenteExport(
            $request->all()
        ))->download('docentes'.$fecha.'.xlsx');
    }
}
