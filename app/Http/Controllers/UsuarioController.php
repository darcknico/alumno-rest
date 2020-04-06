<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\UsuarioSede;
use App\Models\Sede;
use App\Models\Comision;

use App\Functions\CorreoFunction;
use App\Mails\UsuarioPassword as UsuarioPasswordMail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;

class UsuarioController extends Controller
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
      $registros = User::with([
          'tipo',
          'tipoDocumento',
        ]);
      if(strlen($search)==0 and strlen($sort)==0 and strlen($order)==0 and $start==0 ){
        $registros = $registros->get();
        return response()->json($registros, 200);
      }
      $id_tipo_usuario = $request->query('id_tipo_usuario',0);
      $id_sede = $request->query('id_sede',0);
      
      $registros = $registros
        ->when($id_tipo_usuario>0,function($q)use($id_tipo_usuario){
          return $q->where('tus_id',$id_tipo_usuario);
        })
        ->when($id_sede>0,function($q)use($id_sede){
          $usuarios = UsuarioSede::where([
            'estado' => 1,
            'sed_id' => $id_sede,
          ])->pluck('usu_id')->toArray();
          return $q->whereIn('usu_id',$usuarios);
        });
      $values = explode(" ", $search);
      if(count($values)>0){
        foreach ($values as $key => $value) {
          if(strlen($value)>0){
            $registros = $registros->where(function($query) use  ($value) {
              $query->where('usu_nombre','like','%'.$value.'%')
                ->orWhere('usu_email','like','%'.$value.'%')
                ->orWhere('usu_apellido','like','%'.$value.'%')
                ->orWhere('usu_telefono','like','%'.$value.'%')
                ->orWhere('usu_email','like','%'.$value.'%')
                ->orWhere('usu_direccion','like','%'.$value.'%')
                ->orWhere('usu_documento',$value)
                ->orWhereIn('tus_id',function($q)use($value){
                  $q->select('tus_id')->from('tbl_tipo_usuarios')->where('tus_nombre','like','%'.$value.'%');
                });
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

    public function login(Request $request){
      $validator = Validator::make($request->all(),[
        'email' => 'required|email',
        'password' => 'required|string',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>'Email o Contraseña Incorrectos'],401);
      }
      if(Auth::attempt(['usu_email'=> request('email'),'password'=>request('password')]) ){
        $user = Auth::user();
        $usuario = User::with('tipo','tipoDocumento')->where('usu_id',$user->id)->first();
        if($usuario->estado == 0){
        	return response()->json(['error'=>'El usuario no se encuentra habilitado para ingresar al sistema.'],401);
        }
        $usuario['token'] = $user->createToken('MyApp')->accessToken;
        $sede = UsuarioSede::where([
          'estado' => 1,
          'usu_id' => $usuario->usu_id,
        ])->orderBy('updated_at','desc')->first();
        if($sede){
          $usuario['id_sede'] = $sede->sed_id;
        } else {
          return response()->json(['error'=>'No tiene asignada ninguna sede para realizar operaciones. Contacte al administrador del sistema.'],401);
        }
        return response()->json($usuario,200);
      } else {
        return response()->json(['error'=>'Email o Contraseña Incorrectos'],401);
      }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'mensaje' => 'Sesion cerrada'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
      /*
        $validator = Validator::make($request->all(),[
          'email' => 'required | email',
          'password' => 'required',
          'c_password' => 'required | same:password',
          'nombre' => 'required',
          'apellido' => 'required',
        ]);
        if($validator->fails()){
          return response()->json(['error'=>$validator->errors()],401);
        }
        $input = $request->all();
        try {
          $user = new User;
          $user->usu_email = $input['email'];
          $user->usu_nombre = $input['nombre'];
          $user->usu_apellido = $input['apellido'];
          $user->usu_password =bcrypt($input['password']);
          $user->save();
        } catch(\Illuminate\Database\QueryException $e){
            $errorCode = $e->errorInfo[1];
            if($errorCode == '1062'){
                return response()->json(['error'=>'Email Duplicado'],401);
            }
        }
        $user['token'] = $user->createToken('MyApp')->accessToken;
        $user['id_sede'] = 0;
        return response()->json($user,200);
        */
      return response()->json(['error'=>'La registración de usuarios no esta habilitada'],401);
    }

    public function concidencias(Request $request){
      $email = $request->query('email','');
      $todo = \DB::table('tbl_usuarios')->selectRaw("
            IFNULL( (SELECT 1 FROM tbl_usuarios WHERE usu_email like '".$email."' LIMIT 1 ) ,0) as coincidencia")
        ->first();
      return response()->json($todo, 200);
    }

    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
      $user = Auth::user();
      $todo = User::with('tipo','tipoDocumento')
        ->where('usu_id',$user->id)
        ->first();
      return response()->json($todo, 200);
    }


    /**
     * Cambia la contraseña actual.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request)
    {
      $user = Auth::user();
      $validator = Validator::make($request->all(),[
        'password' => 'required',
        'c_password' => 'required',
        'n_password' => 'required | same:c_password',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $usuario = User::where('usu_id',$user->id)->first();
      if(password_verify($request->input('password'), $usuario->usu_password)) {
      	$usuario->usu_password = bcrypt($request->input('n_password'));
        $usuario->save();
      	return response()->json([
	        'mensaje'=> 'Contraseña modificada',
	      ],200);
      } else {
      	return response()->json([
	        'error'=> "Contraseña Incorrecta",
	      ],403);
      }
      
    }

    public function changePassword(Request $request,$id_usuario)
    {
      $validator = Validator::make($request->all(),[
        'password' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $password = $request->input('password');
      $notificacion = $request->input('notificacion',false);
      $email = $request->input('email');

      $usuario = User::where('usu_id',$id_usuario)->first();
      if($usuario){
        $usuario->usu_password = bcrypt($password);
        $usuario->save();

        if($notificacion){
          Mail::to($email)->send( new UsuarioPasswordMail($usuario,$password) );
          if (Mail::failures()) {
              $enviado = false;
          } else {
              $enviado = true;
          }
        }
      }
      return response()->json($usuario,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id_usuario
     * @return \Illuminate\Http\Response
     */
    public function show($id_usuario)
    {
      $user = Auth::user();
      $todo = User::with('tipo','tipoDocumento','sedes')
        ->where('usu_id',$id_usuario)
        ->first();
      return response()->json($todo, 200);
    }

    public function store(Request $request)
    {
      $user = Auth::user();
      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
        'email' => 'required | email',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $input = $request->all();
      $todo = new User;
      $todo->usu_email = $input['email'];
      $todo->usu_nombre = $input['nombre'];
      $todo->usu_apellido = $input['apellido'];
      $todo->usu_fecha_nacimiento = $input['fecha_nacimiento'];
      $todo->usu_telefono = $input['telefono'];
      $todo->usu_celular = $input['celular'];
      $todo->usu_direccion = $input['direccion'];
      $todo->usu_direccion_numero = $input['direccion_numero'];
      $todo->usu_direccion_piso = $input['direccion_piso'];
      $todo->usu_direccion_dpto = $input['direccion_dpto'];
      $todo->usu_documento = $input['documento'];
      $todo->tdo_id = $input['id_tipo_documento'];
      $todo->tus_id = $input['id_tipo_usuario'];
      $todo->usu_password = bcrypt('123456');
      $todo->save();

      $sedes = $request->input('sedes',[]);
      foreach ($sedes as $sede) {
        $asociacion = new UsuarioSede;
        $asociacion->usu_id = $todo->usu_id;
        $asociacion->sed_id = $sede['id_sede'];
        $asociacion->usu_id_usuario = $user->usu_id;
        $asociacion->save();
      }

      return response()->json($todo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id_usuario
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id_usuario)
    {
      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
        'apellido' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $input = $request->all();
      $usuario = User::where('usu_id',$id_usuario)->first();
      if($usuario){
        $usuario->usu_nombre = $input['nombre'];
        $usuario->usu_apellido = $input['apellido'];
        $usuario->usu_fecha_nacimiento = $input['fecha_nacimiento'];
        $usuario->usu_telefono = $input['telefono'];
        $usuario->usu_celular = $input['celular'];
        $usuario->usu_direccion = $input['direccion'];
        $usuario->usu_direccion_numero = $input['direccion_numero'];
        $usuario->usu_direccion_piso = $input['direccion_piso'];
        $usuario->usu_direccion_dpto = $input['direccion_dpto'];
        $usuario->usu_documento = $input['documento'];
        $usuario->tdo_id = $input['id_tipo_documento'];
        $usuario->tus_id = $input['id_tipo_usuario'];
        $usuario->save();
      }
      $todo = User::with('tipo','tipoDocumento')
        ->where('usu_id',$id_usuario)->first();
      return response()->json($todo, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
      $user = Auth::user();
      $validator = Validator::make($request->all(),[
        'nombre' => 'required',
        'apellido' => 'required',
      ]);
      if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],403);
      }
      $input = $request->all();
      $usuario = User::where('usu_id',$user->id)->first();
      if($usuario){
        $usuario->usu_nombre = $input['nombre'];
        $usuario->usu_apellido = $input['apellido'];
        $usuario->usu_fecha_nacimiento = $input['fecha_nacimiento'];
        $usuario->usu_telefono = $input['telefono'];
        $usuario->usu_celular = $input['celular'];
        $usuario->usu_direccion = $input['direccion'];
        $usuario->usu_direccion_numero = $input['direccion_numero'];
        $usuario->usu_direccion_piso = $input['direccion_piso'];
        $usuario->usu_direccion_dpto = $input['direccion_dpto'];
        $usuario->usu_documento = $input['documento'];
        $usuario->tdo_id = $input['id_tipo_documento'];
        $usuario->save();
      }
      return response()->json($usuario, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id_usuario
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_usuario)
    {
      $user = User::where('usu_id',$id_usuario)->first();
      if($user){
        $user->estado = 0;
        $user->save();
      }
      return response()->json($user, 200);
    }

    public function desbloquear($id_usuario){
      $user = User::where('usu_id',$id_usuario)->first();
      if($user){
        $user->estado = 1;
        $user->save();
      }
      return response()->json($user, 200);
    }

    public function sede_asociar(Request $request){
      $user = Auth::user();
      $id_usuario = $request->route('id_usuario');
      $id_sede = $request->route('id_sede');

      $usuario = User::find($id_usuario);
      $sede = Sede::find($id_sede);
      if($usuario and $sede){
        $todo = UsuarioSede::where([
          'estado' => 1,
          'sed_id' => $id_sede,
          'usu_id' => $id_usuario,
        ])->first();
        if ($todo) {
          $todo->usu_id_usuario = $user->id;
          $todo->save();
        } else {
          $todo = new UsuarioSede;
          $todo->sed_id = $id_sede;
          $todo->usu_id = $id_usuario;
          $todo->usu_id_usuario = $user->id;
          $todo->save();
        }
        return response()->json($todo,200);
      }
      return response()->json([
          'error'=>'No se han encontrado el Usuario o la Sede',
      ],403);
    }

    public function sede_seleccionar(Request $request){
      $user = Auth::user();
      $id_usuario = $user->usu_id;
      $id_sede = $request->route('id_sede');

      $usuario = User::find($id_usuario);
      $sede = Sede::find($id_sede);
      if($usuario and $sede){
        $todo = UsuarioSede::where([
          'estado' => 1,
          'sed_id' => $id_sede,
          'usu_id' => $id_usuario,
        ])->first();
        if ($todo) {
          $todo->save();
        } else {
          if($user->tus_id == 1){
            $todo = new UsuarioSede;
            $todo->sed_id = $id_sede;
            $todo->usu_id = $id_usuario;
            $todo->usu_id_usuario = $user->id;
            $todo->save();
          }
        }
        return response()->json($todo,200);
      }
      return response()->json([
          'error'=>'No se han encontrado el Usuario o la Sede',
      ],403);
    }

    public function sede_desasociar(Request $request){
      $user = Auth::user();
      $id_usuario = $request->route('id_usuario');
      $id_sede = $request->route('id_sede');

      $usuario = User::find($id_usuario);
      $sede = Sede::find($id_sede);
      if($usuario and $sede){
          $todo = UsuarioSede::where([
              'estado' => 1,
              'sed_id' => $id_sede,
              'usu_id' => $id_usuario,
          ])->first();
          if($todo){
              $todo->estado = 0;
              $todo->save();
          }
          return response()->json($todo,200);
      }
      return response()->json([
          'error'=>'No se han encontrado el Usuario o la Sede',
      ],403);
    }

}
