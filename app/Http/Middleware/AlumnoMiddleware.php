<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Alumno;

class AlumnoMiddleware
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
    $id_alumno = $request->route('id_alumno');
    $todo = Alumno::where([
      'alu_id' => $id_alumno,
      'estado' => 1,
    ])->first();
    if($todo){
      return $next($request);
    }
    return response()->json([
      'error'=>'No existe el alumno.'
    ],404);
    

  }
}
