<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Alumno;
use App\Models\Inscripcion;
use App\Functions\InscripcionFunction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InscripcionActualizar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inscripcion:actualizar {id_sede=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->info('COMENZANDO INSCRIPCION ACTUALIZAR');
        $id_sede = $this->argument('id_sede');
        if($id_sede>0){
            $iteracion = 1;
            $sede = Sede::find($id_sede);
            $cantidad = Inscripcion::where('estado',1)
                ->where('id_sede',$id_sede)
                ->count();
            $this->info('Sede: '.$sede->nombre.' sobre un total de '.$cantidad);
            Inscripcion::where('estado',1)
            ->where('id_sede',$id_sede)
            ->chunk(100,function($items)use(&$iteracion,$cantidad){
                foreach ($items as $item) {
                    InscripcionFunction::actualizar($item);
                }
                Log::info('Iteracion: '.$iteracion);
                $iteracion++;
            });
        } else {
            $this->info('SIN SEDE SELECCIONADA');
        }
        $this->info('TERMINANDO INSCRIPCION ACTUALIZAR');
    }
}
