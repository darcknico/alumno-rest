<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\PlantillaArchivo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Validator;

use App\Mail\DemoEmail;
use Illuminate\Support\Facades\Mail;

class PlantillaController extends Controller
{
  /**
     * Display a listing of the resource.
     *
     * @param  int $id_sede
     * @param  int $id_fideicomiso
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $id_sede = $request->route('id_sede');
      $todo = Plantilla::with('usuario')->select('pla_id','pla_titulo','pla_descripcion','created_at','updated_at','usu_id')->where([
        'sed_id'=>$id_sede,
        'estado'=>1,
      ])->orderBy('titulo','desc')->get();
      return response()->json($todo,200);
    }

    public function buscar(Request $request)
    {
      $id_sede = $request->route('id_sede');
      $length = $request->query('length',0);
      $search = $request->query('search','');
      $values = explode(" ", $search);
      $registros = Plantilla::with('usuario')->select('pla_id','pla_titulo','pla_descripcion','created_at','updated_at','usu_id')
        ->where([
          'sed_id'=>$id_sede,
          'estado' => 1,
        ]);
      if(count($values)>0){
        foreach ($values as $key => $value) {
          if(strlen($value)>1){
            $registros = $registros->where(function($query) use  ($value) {
              $query->where('pla_titulo','like','%'.$value.'%')
                ->orWhere('pla_descripcion','like','%'.$value.'%');
            });
          }
        }
      }
      $registros = $registros->orderBy('pla_titulo','desc');
      if($length>0){
        $registros = $registros->limit($length)->get();
      } else {
        $registros = $registros->get();
      }
      return response()->json($registros,200);
    }

    public function show(Request $request)
    {
      $id_plantilla = $request->route('id_plantilla');
      $todo = Plantilla::with([
                'usuario',
                'archivos' => function($q){
                    $q->where('estado',1);
                }
            ])->where([
        'estado' => 1,
        'pla_id' => $id_plantilla,
      ])->first();
      return response()->json($todo,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id_sede
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
      $todo = new Plantilla;
      $todo->titulo = $titulo;
      $todo->descripcion = $descripcion;
      $todo->cuerpo = $cuerpo;
      $todo->id_sede = $id_sede;
      $todo->id_usuario = $user->id;
      $todo->save();

      return response()->json($todo,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id_sede
     * @param  int $id_plantilla
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
      $user = Auth::user();
      $id_plantilla = $request->route('id_plantilla');
      $validator = Validator::make($request->all(),[
        'titulo' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $titulo = $request->input('titulo');
      $descripcion = $request->input('descripcion');
      $cuerpo = $request->input('cuerpo');

      $todo = Plantilla::find($id_plantilla);
      $todo->titulo = $titulo;
      $todo->descripcion = $descripcion;
      $todo->cuerpo = $cuerpo;
      $todo->save();

      return response()->json($todo,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id_sede
     * @param  int $id_plantilla
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
      $id_plantilla = $request->route('id_plantilla');
      $user = Auth::user();
      $todo = Plantilla::find($id_plantilla);
      $todo->estado = 0;
      $todo->save();

      $archivos = PlantillaArchivo::where([
        'pla_id'=>$id_plantilla,
        'estado'=>$estado,
      ])->get();
      foreach ($archivos as $archivo) {
        $archivo = PlantillaArchivo::find($archivo->id);
        $archivo->estado = 0;
        $archivo->save();
        Storage::delete($archivo->par_dir);
      }

      return response()->json($todo,200);
    }

    public function enviar(Request $request){
      $user = Auth::user();
      $validator = Validator::make($request->all(),[
          'destino' => 'required | email',
      ]);
      if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],403);
      }
      $id = $request->input('id',0);
      $destino = $request->input('destino');
      $cuerpo = $request->input('cuerpo',null);
      if($id>0 and is_null($cuerpo)){
        $plantilla = Plantilla::where('pla_id',$id)->first();
        $adjunto = PlantillaArchivo::where([
          'pla_id' => $id,
          'estado' => 1,
        ])->get();
        $todo = Mail::send('mails.empty',[
            'cuerpo'=> $plantilla->pla_cuerpo,
        ], function($message) use ($destino,$adjunto,$user){
            $message->from($user->email, $user->apellido.' '.$user->nombre);
            $message->replyTo($user->email, $user->apellido.' '.$user->nombre);
            $message->to($destino)->subject('Prueba del Envio de Correo');
            foreach ($adjunto as $adj) {
              $message = $message->attach(
                storage_path("app/{$adj->par_dir}"),
                [
                  "as"=>$adj->par_nombre,
                ]
              );
            }
        });
      } else {
        $todo = Mail::send('mails.empty',[
            'cuerpo'=> $cuerpo,
        ], function($message) use ($destino,$user){
            $message->from($user->email, $user->apellido.' '.$user->nombre);
            $message->replyTo($user->email, $user->apellido.' '.$user->nombre);
            $message->to($destino)->subject('Prueba del Envio de Correo');
        });
      }
      
      return response()->json($todo,200);
    }

    public function archivoAlta(Request $request){
        $user = Auth::user();
        $id_plantilla = $request->route('id_plantilla');
        $todo = null;
        if($request->hasFile('archivo')){
            $archivo = $request->file('archivo');
            $filename = $archivo->store('plantillas/archivos');

            $todo =  new PlantillaArchivo;
            $todo->id_plantilla = $id_plantilla;
            $todo->nombre = $archivo->getClientOriginalName();
            $todo->par_dir = $filename;
            $todo->id_usuario = $user->id;
            $todo->save();

        }
        return response()->json($todo,200);
    }

    public function archivoBaja(Request $request){
        $id_archivo = $request->route('id_archivo');
        $todo = PlantillaArchivo::find($id_archivo);
        $todo->estado = 0;
        $todo->save();
        Storage::delete($todo->par_dir);
        return response()->json($todo,200);
    }

    public function archivo(Request $request){
        $id_archivo = $request->route('id_archivo');
        $todo = PlantillaArchivo::find($id_archivo);
        return response()->download(storage_path("app/{$todo->par_dir}"));
    }
}