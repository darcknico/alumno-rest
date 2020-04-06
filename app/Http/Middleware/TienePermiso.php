<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class TienePermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$tipos)
    {
        if(is_array($tipos)){
            if(in_array(Auth::user()->id_tipo_usuario, $tipos)){
                return $next($request);
            };
        }else{
            if(Auth::user()->id_tipo_usuario == $tipos)
                return $next($request);
        }

        return response()->json([
          'error'=>'No tiene los permisos necesarios para realizar tal accion.'
        ],403);
    }
}
