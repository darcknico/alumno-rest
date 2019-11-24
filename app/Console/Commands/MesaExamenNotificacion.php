<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaDocente;
use App\Models\Academico\Docente;
use App\Models\App\Dispositivo;

use App\Functions\MesaExamenFunction;
use App\Notifications\MesaExamenDocenteAsistencia;
use App\Mails\MesaExamenNotificacion as MesaExamenNotificacionMail;

use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class MesaExamenNotificacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mesa_examen:notificacion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de las notificaciones previo a la hora del examen';

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
        $materias = MesaExamenMateria::whereHas('mesa_examen',function($q){
            $q->where('estado',1)->where(function($qt){
                $qt->where('notificacion_push',true)->orWhere('notificacion_email',true);
            });
        })
        ->where('estado',1)
        ->whereRaw('mma_fecha = NOW() - INTERVAL 30 MINUTE')
        ->get();
        foreach ($materias as $materia) {
            $sede = Sede::find($materia->mesa_examen->id_sede);
            $mesa_examen = MesaExamen::find($materia->mesa_examen->id);
            $docentes = MesaExamenMateriaDocente::where('id_mesa_examen_materia',$materia->id)
            ->where('estado',1)
            ->get();
            foreach ($docentes as $docente) {
                $docentes = Docente::whereIn('usu_id',$docente->id_usuario)->get();
                /*
                *   EMAILS
                */
                if($docente->usuario->email and $materia->notificacion_email){
                    Mail::to($docente->usuario->email)->send( new MesaExamenNotificacionMail($docente,$mesa_examen,$materia) );
                    if (Mail::failures()) {
                        $enviado = false;
                    } else {
                        $enviado = true;
                    }
                }
                /**
                *   PUSH
                */
                if($materia->notificacion_push){
                    $dispositivos = Dispositivo::where('estado',1)->where('id_usuario',$docente->id_usuario)->get();
                    Notification::send($dispositivos, new MesaExamenDocenteAsistencia($materia->id));
                }

            }
            
            
        }
        
    }
}
