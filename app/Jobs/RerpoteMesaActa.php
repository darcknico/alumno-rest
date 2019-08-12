<?php

namespace App\Jobs;

use App\Models\Extra\ReporteJob;
use App\Models\Mesa\TipoCondicionAlumno;
use App\Models\Mesa\MesaExamenMateria;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

use App\Jobs\RerpoteMesaActa;

use Carbon\Carbon;
use JasperPHP\JasperPHP;
use Chumper\Zipper\Zipper;

class RerpoteMesaActa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200; // 2 hours

    protected $ids;
    protected $reporte;
    protected $contador;
    protected $carpeta = null;
    protected $identificador = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ids,ReporteJob $reporte)
    {
        $this->ids=$ids;
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
        $jasper = new JasperPHP;
        if(is_null($this->identificador)){
            $this->identificador = uniqid();
        }
        if(is_null($this->carpeta)){
            $this->carpeta = "reportes/".$this->identificador."/";
            Storage::makeDirectory($this->carpeta,0777,true);
        }
        $input = storage_path("app/reportes/alumno_mesa_acta.jasper");
        $ext = "pdf";

        $zip_file = "reportes/rj".$this->identificador.'.zip';
        $zipper = new Zipper;

        $zipper->make(storage_path('app/'.$zip_file));

        $tipos = TipoCondicionAlumno::where('estado',1)->get();
        $comienzo = 0;
        if($this->contador>0){
            $comienzo = $this->contador;
        }
        $i = 0;
        for ($i=$comienzo ; $i < count($this->ids) ; $i++) {
            $id = $this->ids[$i];
            $mesa_examen_materia = MesaExamenMateria::find($id);
            foreach ($tipos as $tipo) {
                $acta = "app/".$this->carpeta.$mesa_examen_materia->materia->codigo.'_'.$tipo->nombre;
                $output = storage_path($acta);
                $jasper->process(
                    $input,
                    $output,
                    [$ext],
                    [
                        'REPORT_LOCALE' => 'es_AR',
                        'id_mesa_examen_materia' => $id,
                        'id_tipo_condicion_alumno' => $tipo->id,
                        'id_usuario' => $this->reporte->id_usuario,
                        'logo' => storage_path("app/images/logo_2.png"),
                    ],
                    \Config::get('database.connections.mysql')
                )->execute();
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
