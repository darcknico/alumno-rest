<?php

namespace App\Jobs;

use App\Models\Sede;
use App\Models\Extra\ReporteJob;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaDocente;
use App\Models\Academico\Docente;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Functions\AuxiliarFunction;
use App\Filters\MesaExamenMateriaDocenteFilter;
use App\Mails\NotificacionDocente as NotificacionDocenteMail;

use Carbon\Carbon;
use JasperPHP\JasperPHP;
use Chumper\Zipper\Zipper;

class ReporteNotificacionDocente implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200; // 2 hours

    protected $id_sede;
    protected $fecha_inicial;
    protected $fecha_final;
    protected $reporte;
    protected $contador;
    protected $carpeta = null;
    protected $identificador = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id_sede,Carbon $fecha_inicial, Carbon $fecha_final,ReporteJob $reporte)
    {
        $this->id_sede=$id_sede;
        $this->fecha_inicial=$fecha_inicial;
        $this->fecha_final=$fecha_final;
        $this->reporte=$reporte;
        $this->contador = 0;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id_sede = $this->id_sede;
        $fecha_inicial = $this->fecha_inicial;
        $fecha_final = $this->fecha_final;
        $sede = Sede::find($id_sede);

        $jasper = new JasperPHP;
        if(is_null($this->identificador)){
            $this->identificador = uniqid();
        }
        if(is_null($this->carpeta)){
            $this->carpeta = "reportes/".$this->identificador."/";
            Storage::makeDirectory($this->carpeta,0777,true);
        }
        $input = storage_path("app/reportes/alumno_docente_mesa.jasper");
        $ext = "pdf";

        $zip_file = "reportes/rj".$this->identificador.'.zip';
        $zipper = new Zipper;

        $zipper->make(storage_path('app/'.$zip_file));

        $comienzo = 0;
        if($this->contador>0){
            $comienzo = $this->contador;
        }

        $registros = MesaExamenMateriaDocente::whereHas('mesa_examen_materia',function($q)use($id_sede){
                $q->whereHas('mesa_examen',function($qt)use($id_sede){
                    $qt->where([
                        'estado' => 1,
                        'sed_id' => $id_sede,
                    ]);
                })
                ->where('estado',1);
            })
            ->where([
            'estado' => 1,
        ]);
        $registros = MesaExamenMateriaDocenteFilter::fill([
            'fecha_inicial' => $this->fecha_inicial->toDateString(),
            'fecha_final' => $this->fecha_final->toDateString(),
        ],$registros);
        $registros = $registros->get()->pluck('usu_id');

        $docentes = Docente::whereIn('usu_id',$registros)->get();

        $diff = $fecha_inicial->diffInMonths($fecha_final);
        $periodo = "";
        $periodo_nombre = "";
        if($diff>0){
            if($fecha_inicial->year == $fecha_final->year){
                $periodo = $fecha_inicial->formatLocalized('%B')." / ".$fecha_final->formatLocalized('%B')." ".$fecha_final->year;
                $periodo_nombre = $fecha_inicial->formatLocalized('%B')."_".$fecha_final->formatLocalized('%B')."_".$fecha_final->year;
            } else {
                $periodo = $fecha_inicial->formatLocalized('%B')." ".$fecha_inicial->year." / ".$fecha_final->formatLocalized('%B')." ".$fecha_final->year;
                $periodo_nombre = $fecha_inicial->formatLocalized('%B')."_".$fecha_inicial->year."_".$fecha_final->formatLocalized('%B')."_".$fecha_final->year;
            }
        } else {
            $periodo = $fecha_inicial->formatLocalized('%B')." ".$fecha_inicial->year;
            $periodo_nombre = $fecha_inicial->formatLocalized('%B')."_".$fecha_inicial->year;
        }

        $i = 0;
        foreach ($docentes as $docente) {
            $i = $i + 1;
            $filename = "";
            if(is_null($docente->usuario->documento) or empty($docente->usuario->documento) ){
                $filename = AuxiliarFunction::sanitize_file_name($docente->usuario->apellido.$docente->usuario->nombre);
            } else {
                $tipoDocumento = "";
                if($docente->usuario->tipoDocumento){
                    $tipoDocumento = $docente->usuario->tipoDocumento->nombre;
                } 
                $filename = $tipoDocumento.$docente->usuario->documento;
            }
            
            $acta = "app/".$this->carpeta.$filename.'_'.$periodo_nombre;
            $output = storage_path($acta);
            $jasper->process(
                $input,
                $output,
                [$ext],
                [
                    'REPORT_LOCALE' => 'es_AR',
                    'id_usuario' => $docente->id_usuario,
                    'fecha_inicial' => $fecha_inicial->toDateString(),
                    'fecha_final' => $fecha_final->toDateString(),
                    'periodo' => $periodo,
                    'id_sede' => $id_sede,
                    'logo' => storage_path("app/images/logo_2.png"),
                ],
                \Config::get('database.connections.mysql')
            )->execute();

            if(!empty($docente->usuario->email)){

                Mail::to($docente->usuario->email)->send( new NotificacionDocenteMail($docente,$sede,$periodo,$output.'.'.$ext) );
                if (Mail::failures()) {
                    $enviado = false;
                } else {
                    $enviado = true;
                }
            }

            $this->contador = $i;
            $this->reporte->contador = $i;
            $this->reporte->save();
        }
        $this->contador = $i;
        $this->reporte->contador = $i;
        $this->reporte->save();

        $zipper->add(storage_path('app/'.$this->carpeta))->close();
        
        Storage::deleteDirectory($this->carpeta);
        $this->reporte->rjo_dir = $zip_file;
        $this->reporte->terminado = Carbon::now();
        $this->reporte->save();
    }

/*
    public function failed(Exception $exception)
    {
        $this->reporte->terminado = Carbon::now();
        $this->reporte->save();
    }
    */
}
