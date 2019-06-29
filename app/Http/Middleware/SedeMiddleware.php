<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioSede;

class SedeMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    $id_sede = $request->route('id_sede');
    $user = Auth::user();
    if($user->tus_id==1){
      return $next($request);
    }
    $empresa = UsuarioSede::where([
      'sed_id' => $id_sede,
      'usu_id' => $user->id,
      'estado' => 1,
    ])->first();
    if (!$empresa) {
        return response()->json([
          'error'=>'No tiene asignada la sede para realizar operaciones.'
        ],401);
    }
    return $next($request);

  }
}
