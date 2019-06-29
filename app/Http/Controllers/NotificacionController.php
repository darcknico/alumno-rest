<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Notificacion;
use App\Models\Plantilla;
use App\Models\PlantillaArchivo;
use App\Models\Alumno;
use App\Models\AlumnoNotificacion;
use App\Models\Carrera;
use App\Models\Inscripcion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

use App\Functions\AuxiliarFunction;
use App\Functions\CorreoFunction;
use Glide;
use Carbon\Carbon;

use Illuminate\Contracts\Filesystem\Filesystem;

class NotificacionController extends Controller
{
    public function images(Filesystem $filesystem, $path)
    {
      $token = request()->query('token','');
      if(!empty($token)){
        $token = AlumnoNotificacion::where([
          'ano_token'=>$token,
          'estado'=>1,
        ])->first();
        $token->ano_visto = Carbon::now();
        $token->save();
      }

      return Glide::server('images')->imageResponse($path, request()->all());
    }

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
      $todo = Notificacion::with('usuario')->where([
        'sed_id'=>$id_sede,
        'not_enviado'=>0,
        'estado'=>1,
      ])->orderBy('created_at','desc')->get();
      return response()->json($todo,200);
    }

    public function enviadas(Request $request)
    {
      $id_sede = $request->route('id_sede');
      $todo = Notificacion::with('usuario')->where([
        'sed_id'=>$id_sede,
        'not_enviado'=>1,
        'estado'=>1,
      ])->orderBy('created_at','desc')->get();
      return response()->json($todo,200);
    }

    public function show(Request $request)
    {
      $id_sede = $request->route('id_sede');
      $id_notificacion = $request->route('id_notificacion');
      $todo = Notificacion::with([
        'alumnos'=>function($q){
          $q->where('estado',1);
        },
        'usuario',
      ])->where([
        'sed_id' => $id_sede,
        'not_id' => $id_notificacion,
      ])->first();
      return response()->json($todo,200);
    }

    public function alumnos(Request $request){
      $id_sede = $request->route('id_sede');
      $id_notificacion = $request->route('id_notificacion');
      $todo = Alumno::whereHas('notificaciones',function($q)use($id_notificacion){
        $q->where([
          'estado' => 1,
          'not_id' => $id_notificacion,
        ]);
      })->where('sed_id',$id_sede)->get();
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
        'nombre' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $alumnos_asociados = $request->input('alumnos_asociados',[]);
      $nombre = $request->input('nombre');
      $descripcion = $request->input('descripcion');
      $asunto = $request->input('asunto');
      $responder_email = $request->input('responder_email');
      $responder_nombre = $request->input('responder_nombre');
      $fecha = $request->input('fecha');
      $id_plantilla = $request->input('id_plantilla');

      $todo = new Notificacion;
      $todo->nombre = $nombre;
      $todo->descripcion = $descripcion;
      $todo->asunto = $asunto;
      $todo->responder_email = $responder_email;
      $todo->responder_nombre = $responder_nombre;
      $todo->fecha = $fecha;
      $todo->id_plantilla = $id_plantilla;
      $todo->id_sede = $id_sede;
      $todo->id_usuario = $user->id;
      $todo->save();

      foreach ($alumnos_asociados as $asociado) {
        $alumno = new AlumnoNotificacion;
        $alumno->id_notificacion = $todo->id;
        $alumno->id_alumno = $asociado['id'];
        $alumno->id_usuario = $user->id;
        $alumno->email = $asociado['email'];
        $alumno->save();
      }
      return response()->json($todo,200);
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int $id_sede
    * @param  int $id_notificacion
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request)
    {
      $user = Auth::user();
      $id_notificacion = $request->route('id_notificacion');

      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $alumnos_asociados = $request->input('alumnos_asociados',[]);
      $nombre = $request->input('nombre');
      $descripcion = $request->input('descripcion');
      $asunto = $request->input('asunto');
      $responder_email = $request->input('responder_email');
      $responder_nombre = $request->input('responder_nombre');
      $fecha = $request->input('fecha');
      $id_plantilla = $request->input('id_plantilla');

      $todo = Notificacion::find($id_notificacion);
      $todo->nombre = $nombre;
      $todo->descripcion = $descripcion;
      $todo->asunto = $asunto;
      $todo->responder_email = $responder_email;
      $todo->responder_nombre = $responder_nombre;
      $todo->fecha = $fecha;
      $todo->id_plantilla = $id_plantilla;
      $todo->save();

      $asociados = AlumnoNotificacion::where([
        'not_id' => $id_notificacion,
        'estado' => 1,
      ])->get()->toArray();
      foreach ($asociados as $asociado) {
        if(!AuxiliarFunction::if_in_array($alumnos_asociados,$asociado,"id","id_alumno")){
          $alumno = AlumnoNotificacion::find($asociado->id);
          $alumno->estado = 0;
          $alumno->save();
        }
      }
      $asociados = AlumnoNotificacion::where([
        'not_id' => $id_notificacion,
        'estado' => 1,
      ])->get()->toArray();
      foreach ($alumnos_asociados as $asociado) {
        if(!AuxiliarFunction::if_in_array($asociados,$asociado,"id_alumno","id")){
          $alumno = new AlumnoNotificacion;
          $alumno->id_notificacion = $id_notificacion;
          $alumno->id_alumno = $value['id'];
          $alumno->id_usuario = $user->id;
          $alumno->email = $value['email'];
          $alumno->save();
        }
      }
      return response()->json($todo,200);
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int $id_sede
    * @param  int $id_notificacion
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request)
    {
      $user = Auth::user();
      $id_notificacion = $request->route('id_notificacion');

      $todo = Notificacion::find($id_notificacion);
      $todo->estado = 0;
      $todo->save();

      AlumnoNotificacion::where('not_id',$id_notificacion)->update([
        'estado' => 0,
      ]);

      return response()->json($todo,200);
    }

    public function filtrar(Request $request){
      $id_sede = $request->route('id_sede');

      $minimo_edad = $request->input('minimo_edad',null);
      $maximo_edad = $request->input('maximo_edad',null);
      $sexo = $request->input('sexo',null);
      $id_departamento = $request->input('id_departamento',0);
      $id_carrera = $request->input('id_carrera',0);
      $id_tipo_alumno_estado = $request->input('id_tipo_alumno_estado',0);

      $olgado = $request->input('olgado',false);

      $base = Alumno::where([
        'sed_id'=>$id_sede,
        'estado'=>1,
      ])->whereNotNull('alu_email');
      if(!$olgado){
        if(!is_null($sexo)){
          if($sexo != "A"){
            $base = $base->where('alu_sexo',$sexo);
          }
        }

        if(!is_null($minimo_edad) or !is_null($maximo_edad)){
          $base = $base->whereNotNull('alu_fecha_nacimiento');
          if(is_null($minimo_edad)){
            $base = $base->whereRaw('TIMESTAMPDIFF(YEAR,alu_fecha_nacimiento,CURDATE()) <= '.$maximo_edad);
          } else if(is_null($maximo_edad)){
            $base = $base->whereRaw('TIMESTAMPDIFF(YEAR,alu_fecha_nacimiento,CURDATE()) >= '.$minimo_edad);
          } else {
            $base = $base->whereBetween(\DB::raw('TIMESTAMPDIFF(YEAR,alu_fecha_nacimiento,CURDATE())'),[$maximo_edad,$minimo_edad]);
          }
        }

        $base = $base
          ->when($id_departamento>0,function($q)use($id_departamento){
            $carreras = Carrera::where([
                'dep_id' => $id_departamento,
                'estado' => 1,
            ])->pluck('car_id')->toArray();
            $inscripciones = Inscripcion::where([
                'estado' => 1,
            ])
            ->whereIn('car_id',$carreras)
            ->pluck('alu_id')->toArray();
            return $q->whereIn('alu_id',$inscripciones);
          })
          ->when($id_carrera>0,function($q)use($id_carrera){
            $inscripciones = Inscripcion::where([
                'car_id' => $id_carrera,
                'estado' => 1,
            ])->pluck('alu_id')->toArray();
            return $q->whereIn('alu_id',$inscripciones);
          })
          ->when($id_tipo_alumno_estado>0,function($q)use($id_tipo_alumno_estado){
            return $q->where('tae_id',$id_tipo_alumno_estado);
          });
        return response()->json($base->get(),200);
      }

      $uniones = [];
      $filtro = clone($base->getQuery());
      if(!is_null($sexo)){
        $aux = clone($filtro);
        if($sexo != "A"){
          $aux = $aux->where('alu_sexo',$sexo);
        }
        $base->unionAll($aux);
      }

      if(!is_null($minimo_edad) or !is_null($maximo_edad)){
        $aux = clone($filtro);
        if(is_null($minimo_edad)){
          $maximo_edad = Carbon::now()->subYears($maximo_edad);
          $aux = $aux->where('alu_fecha_nacimiento','>=',$maximo_edad);
        } else if(is_null($maximo_edad)){
          $minimo_edad = Carbon::now()->subYears($minimo_edad);
          $aux = $aux->where('alu_fecha_nacimiento','<=',$minimo_edad);
        } else {
          $minimo_edad = Carbon::now()->subYears($minimo_edad);
          $maximo_edad = Carbon::now()->subYears($maximo_edad);
          $aux = $aux->whereBetween('alu_fecha_nacimiento',[$maximo_edad,$minimo_edad]);
        }
        $base = $base->unionAll($aux);
      }

      $table = $this->getEloquentSqlWithBindings($base);
      $todo = \DB::table(\DB::raw('('.$table.') as alu'))
        ->selectRaw('alu.alu_id as id, alu.alu_nombre as nombre, alu.alu_apellido as apellido, alu.alu_documento as documento, (count(alu.alu_id)-1) as coincidencias')
        ->groupBy(\DB::raw('alu.alu_id,alu.alu_nombre,alu.alu_apellido,alu.alu_documento'))->havingRaw('(count(alu.alu_id)-1)>0')->orderBy('coincidencias','desc')->get();
      return response()->json($todo,200);
    }

    public static function getEloquentSqlWithBindings($query)
    {
      return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
        return is_numeric($binding) ? $binding : "'{$binding}'";
      })->toArray());
    }


    public function fecha(Request $request){
      $user = Auth::user();
      $id_sede = $request->route('id_sede');
      $id_notificacion = $request->route('id_notificacion');

      $validator = Validator::make($request->all(),[
        'fecha' => 'required | date',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $fecha = $request->input('fecha');
      $old = Notificacion::find($id_notificacion);

      $todo = new Notificacion;
      $todo->nombre = $old->nombre;
      $todo->descripcion = $old->descripcion;
      $todo->asunto = $old->asunto;
      $todo->responder_email = $old->responder_email;
      $todo->responder_nombre = $old->responder_nombre;
      $todo->fecha = $fecha;
      $todo->id_plantilla = $old->id_plantilla;
      $todo->id_sede = $old->id_sede;
      $todo->id_usuario = $user->id;
      $todo->save();

      $asociados = AlumnoNotificacion::where([
        'estado' => 1,
        'not_id' => $id_notificacion,
      ])->get();
      foreach ($asociados as $asociado) {
        $alumno = new AlumnoNotificacion;
        $alumno->id_notificacion = $id_notificacion;
        $alumno->id_alumno = $asociado->id_alumno;
        $alumno->id_usuario = $user->id;
        $alumno->email = $asociado->email;
        $alumno->save();
      }

      return response()->json($todo,200);
    }

    public function desplegar(Request $request){
      $id_notificacion = $request->route('id_notificacion');
      $notificacion = Notificacion::find($id_notificacion);
      $alumnos = AlumnoNotificacion::where([
        'estado' => 1,
        'not_id' => $id_notificacion,
        'ano_enviado' => 0,
      ])->get();
      $plantilla = Plantilla::find($notificacion->id_plantilla);
      foreach ($alumnos as $alumno) {
        $token = bin2hex(random_bytes(64));
        $logo = CorreoFunction::logo();
        $visto = $logo."?w=25&h=25&token=".$token;
        $envio = AlumnoNotificacion::find($alumno->id);
        
        $adjunto = PlantillaArchivo::where([
          'pla_id' => $notificacion->pla_id,
          'estado' => 1,
        ])->get();
        try {
          \Mail::send('mails.notificacion',[
            'cuerpo' => $plantilla->cuerpo,
            'visto' => $visto,
          ], function($message)use($alumno,$notificacion,$adjunto){
            $message->from($notificacion->responder_email, $notificacion->responder_nombre);
            $message->to($alumno->email)->subject($notificacion->asunto);
            foreach ($adjunto as $adj) {
              $message = $message->attach(
                storage_path("app/{$adj->par_dir}"),
                [
                  "as"=>$adj->nombre,
                ]
              );
            }
          });

          if (\Mail::failures()) {
          } else {
            $envio->ano_token = $token;
          }
        } catch (\Exception $e) {

        }
        $envio->enviado = 1;
        $envio->save();
      }
      $notificacion->enviado = 1;
      $notificacion->fecha = Carbon::now();
      $notificacion->save();

      return response()->json($alumnos,200);
    }

}
