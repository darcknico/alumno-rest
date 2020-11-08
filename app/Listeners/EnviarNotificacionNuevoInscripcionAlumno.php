<?php

namespace App\Listeners;

use App\Models\Sede;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Models\PlanEstudio;
use App\Models\Materia;
use App\Models\AlumnoNotificacion;
use App\Models\Carrera;
use App\Functions\CorreoFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionNuevoInscripcionAlumno implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $alumno = Alumno::with('tipoDocumento','provincia')->find($event->id_alumno);
        if($alumno->email){
            $inscripcion = Inscripcion::find($event->id_inscripcion);
            $sede = Sede::find($event->id_sede);
            $carrera = Carrera::find($event->id_carrera);
            $plan_estudio = PlanEstudio::find($event->id_plan_estudio);
            $materias = Materia::where([
                'pes_id' => $event->id_plan_estudio,
                'estado' => 1,
            ])->orderBy('tml_id','asc')->get();
            $token = bin2hex(random_bytes(64));
            $logo = CorreoFunction::logo();
            $logo = $logo."?token=".$token;
                Mail::send('mails.inscripcion',[
                    'logo' => $logo,
                    'alumno' => $alumno,
                    'carrera' => $carrera,
                    'plan_estudio' => $plan_estudio,
                    'materias' => $materias,
                    'sede' => $sede,
                ], function($message) use ($carrera,$alumno){
                    $message->from('informes@ariasdesaavedra.edu.ar', 'informes');
                    $message->replyTo("no-replay@prueba.com","No Responder");
                    $message->to($alumno->email)->subject("Inscripcion a Carrera ".$carrera->nombre);
                    
                });
                
                if (Mail::failures()) {
                    $enviado = false;
                } else {
                    $enviado = true;
                }
            $notificacion = new AlumnoNotificacion;
            $notificacion->alu_id = $id_alumno;
            $notificacion->usu_id = $inscripcion->id_usuario;
            $notificacion->ano_enviado = $enviado;
            $notificacion->ano_token = $token;
            $notificacion->ano_email = $alumno->email;
            $notificacion->save();
        }
        
        return true;
    }
}
