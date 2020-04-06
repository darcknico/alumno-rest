<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Plantilla;
use App\Models\Notificacion;
use App\Models\AlumnoNotificacion;
use App\Models\AlumnoDispositivo;
use App\Functions\CorreoFunction;
use Carbon\Carbon;

use App\Notifications\NotificacionNueva;

use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

class NotificacionEnviar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificacion:enviar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de la notifiaciones de correo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notificaciones = Notificacion::where([
            ['not_fecha', '>=', Carbon::now()->second(0)],
            ['not_fecha', '<=', Carbon::now()->second(59)]
        ])->where([
            'not_enviado' => 0,
            'estado' => 1,
        ])->get();
        foreach ($notificaciones as $notificacion) {
            $notificacion = Notificacion::find($notificacion->id);
            $alumnos = AlumnoNotificacion::where([
                'estado' => 1,
                'not_id' => $notificacion->id,
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
                if($notificacion->puede_email){
                  try {
                    Mail::send('mails.notificacion',[
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
                }

                if($notificacion->puede_push){
                  $dispositivos = AlumnoDispositivo::where('estado',1)
                    ->where('id_alumno',$alumno->id)->get();
                  foreach ($dispositivos as $dispositivo) {
                    $dispositivo->notify(new NotificacionNueva($notificacion));
                  }
                }
                
                $envio->enviado = 1;
                $envio->save();
            }
            $notificacion->enviado = 1;
            $notificacion->fecha = Carbon::now();
            $notificacion->save();
        }
    }
}
