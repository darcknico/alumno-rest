<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable,Eloquence, Mappable;

    protected $table ='tbl_usuarios';
    protected $primaryKey = 'usu_id';
    protected $rememberTokenName = 'usu_token';

  protected $with = [
    'docente',
    'tipo',
    'tipoDocumento',
  ];
/*
    protected $fillable = [
      'usu_email',
      'usu_password',
      'usu_nombre',
      'usu_apellido',
      'usu_fecha_nacimiento',
      'usu_telefono',
      'usu_celular',
      'usu_direccion',
      'usu_direccion_numero',
      'usu_direccion_piso',
      'usu_direccion_dpto',
      'usu_documento',
      'tdo_id',
      'tus_id',
  ];
*/
  protected $casts = [
      'estado'=>'boolean',
  ];

  protected $hidden = [
      'usu_password',
      'usu_email_verificado',
      'usu_email',
      'usu_nombre',
      'usu_apellido',
      'usu_fecha_nacimiento',
      'usu_telefono',
      'usu_celular',
      'usu_direccion',
      'usu_direccion_numero',
      'usu_direccion_piso',
      'usu_direccion_dpto',
      'usu_documento',
      'tdo_id',
      'tus_id',
      'usu_id',
      'usu_token',
  ];

  protected $maps = [
      'email' => 'usu_email',
      'nombre' => 'usu_nombre',
      'apellido' => 'usu_apellido',
      'fecha_nacimiento' => 'usu_fecha_nacimiento',
      'telefono' => 'usu_telefono',
      'celular' => 'usu_celular',
      'direccion' => 'usu_direccion',
      'direccion_numero' => 'usu_direccion_numero',
      'direccion_piso' => 'usu_direccion_piso',
      'direccion_dpto' => 'usu_direccion_dpto',
      'documento' => 'usu_documento',
      'id_tipo_documento' => 'tdo_id',
      'id_tipo_usuario' => 'tus_id',
      'id' => 'usu_id',
  ];

  protected $appends = [
    'email',
    'nombre',
    'apellido',
    'fecha_nacimiento',
    'telefono',
    'celular',
    'direccion',
    'direccion_numero',
    'direccion_piso',
    'direccion_dpto',
    'documento',
    'id_tipo_documento',
    'id_tipo_usuario',
    'id',
  ];

  public function getAuthPassword()
  {
    return $this->usu_password;
  }

  public function tipo(){
    return $this->hasOne('App\Models\TipoUsuario','tus_id','tus_id');
  }

  public function tipoDocumento(){
    return $this->hasOne('App\Models\TipoDocumento','tdo_id','tdo_id');
  }

  public function sedes(){
    return $this->hasMany('App\Models\UsuarioSede','usu_id','usu_id');
  }

  public function docente(){
    return $this->hasOne('App\Models\Academico\Docente','usu_id','usu_id');
  }

}
