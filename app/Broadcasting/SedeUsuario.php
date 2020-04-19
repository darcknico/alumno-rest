<?php

namespace App\Broadcasting;

use App\User;
use App\Models\UsuarioSede;

class SedeUsuario
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user,$id_sede)
    {
        if($user->tus_id==1){
          return true;
        }
        $sede = UsuarioSede::where([
          'sed_id' => $id_sede,
          'usu_id' => $user->id,
          'estado' => 1,
        ])->first();
        if ($sede) {
            return true;
        } else {
            return false;
        }
    }
}
