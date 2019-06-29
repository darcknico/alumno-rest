<?php

namespace App\Console\Commands;

use App\Models\Sede;

use App\Functions\DiariaFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class DiariaCerrar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diaria:cerrar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierre de la ultima diaria abierta para todas las sedes';

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
        $ahora = Carbon::now();
        $sedes = Sede::where('estado',1)->get();
        foreach ($sedes as $sede) {
            $diaria = DiariaFunction::cerrar($sede->id,$ahora);
        }
    }
}
