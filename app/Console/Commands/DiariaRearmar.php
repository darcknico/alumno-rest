<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Movimiento;

use App\Functions\DiariaFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class DiariaRearmar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diaria:rearmar {id_sede=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Para todas las sedes, realiza un paso por cada diaria existente para volver a contabilizar los montos';

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
                $most_old = Movimiento::where([
                    'estado' => 1,
                    'sed_id' => $sede->id,
                ])->orderBy('fecha','asc')
                ->first();
                if($most_old){
                    $pivot = Carbon::parse($most_old->fecha)->startOfMonth();
                    $this->info('Sede: '.$sede->nombre." Fecha: ".$pivot->toDateString());
                    $hoy = Carbon::now();
                    while ( $pivot->isBefore($hoy) ) {
                        $diarias = DiariaFunction::actualizar($sede->id,$pivot);
                        $pivot = $pivot->addMonth();
                        $this->info('Cantidad: '.count($diarias).' Siguiente: '.$pivot->toDateString());
                    }
                }
            }
        } else {
            $sedes = Sede::where('estado',1)->get();
            foreach ($sedes as $sede) {
                $most_old = Movimiento::where([
                    'estado' => 1,
                    'sed_id' => $sede->id,
                ])->orderBy('fecha','asc')
                ->first();
                if($most_old){
                    $pivot = Carbon::parse($most_old->fecha)->startOfMonth();
                    $this->info('Sede: '.$sede->nombre." Fecha: ".$pivot->toDateString());
                    $hoy = Carbon::now();
                    while ( $pivot->isBefore($hoy) ) {
                        $diarias = DiariaFunction::actualizar($sede->id,$pivot);
                        $pivot = $pivot->addMonth();
                        $this->info('Cantidad: '.count($diarias).' Siguiente: '.$pivot->toDateString());
                    }
                }
            }
            return $sedes;
        }
        
    }
}
