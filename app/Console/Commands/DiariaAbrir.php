<?php

namespace App\Console\Commands;

use App\Models\Sede;

use App\Functions\DiariaFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class DiariaAbrir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diaria:abrir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Abre una diaria para todas las sedes';

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
            $diaria = DiariaFunction::abrir($sede->id,$ahora);
        }
    }
}
