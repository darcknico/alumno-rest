<?php

namespace App\Console;

use App\Models\Sede;
use App\Models\Plantilla;
use App\Models\Notificacion;
use App\Models\AlumnoNotificacion;
use App\Functions\CorreoFunction;
use App\Functions\DiariaFunction;
use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\DiariaAbrir',
        'App\Console\Commands\DiariaCerrar',
        'App\Console\Commands\DiariaRearmar',
        'App\Console\Commands\NotificacionEnviar',
        'App\Console\Commands\PlanPagoInteres',
        'App\Console\Commands\PlanPagoRearmar',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        
        $schedule->command('backup:run')->daily()->at('02:00');

        $schedule->command('notificacion:enviar')->everyMinute();

        $schedule->command('plan_pago:interes')->monthlyOn(1,'1:00');

        //ABRIR DIARIA
        //$schedule->command('diaria:abrir')->daily()->at('02:00');

        //CERRAR DIARIA
        //$schedule->command('diaria:cerrar')->daily()->at('23:00');
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
