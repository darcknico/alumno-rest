<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Comision;
use App\Models\Comision\Examen;
use App\Functions\ComisionFunction;

class ComisionActualizar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comision:actualizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza los estados de todas las comisiones y sus Examenes';

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
        $comisiones = Comision::where('estado',1)->get();
        foreach ($comisiones as $comision) {
            ComisionFunction::actualizar($comision);
            $examenes = Examen::where('com_id',$comision->id)->where('estado',1)->get();
            foreach ($examenes as $examen) {
                ComisionFunction::examenActualizar($examen);
            }
        }
    }
}
