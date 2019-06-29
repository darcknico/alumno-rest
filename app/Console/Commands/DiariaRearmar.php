<?php

namespace App\Console\Commands;

use App\Models\Sede;

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
    protected $signature = 'diaria:rearmar';

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
        $sedes = Sede::where('estado',1)->get();
        foreach ($sedes as $sede) {
            DiariaFunction::actualizar($sede->id);
        }
        return $sedes;
    }
}
