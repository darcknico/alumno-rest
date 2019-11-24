<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Mesa\MesaExamen;

use App\Functions\MesaExamenFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class MesaExamenActualizar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mesa_examen:actualizar {id_sede=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Para todas las sedes, para todas sus mesas de examen, actualizar sus estados';

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
        $id_sede = $this->argument('id_sede');
        if( $id_sede>0 ){
            $sede = Sede::find($id_sede);
            if($sede){
                $this->info('Sede: '.$sede->nombre);
                $mesas = MesaExamen::where('id_sede',$sede->id)
                    ->where('estado',1)
                    ->get();
                foreach ($mesas as $mesa) {
                    $mesa = MesaExamen::find($mesa->id);
                    MesaExamenFunction::actualizar($mesa,true);
                }
            }
        } else {
            $sedes = Sede::where('estado',1)->get();
            foreach ($sedes as $sede) {
                $this->info('Sede: '.$sede->nombre);
                $mesas = MesaExamen::where('id_sede',$sede->id)
                    ->where('estado',1)
                    ->get();
                foreach ($mesas as $mesa) {
                    $mesa = MesaExamen::find($mesa->id);
                    MesaExamenFunction::actualizar($mesa,true);
                }
            }
            return $sedes;
        }
        
    }
}
