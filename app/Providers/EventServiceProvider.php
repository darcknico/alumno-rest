<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\InscripcionAlumnoNuevo' => [
            'App\Listeners\ActualizarAlumno',
            'App\Listeners\EnviarNotificacionNuevoInscripcionAlumno',
        ],
        'App\Events\InscripcionComisionNuevo' => [
            'App\Listeners\ActualizarComision',
            'App\Listeners\ActualizarPeriodoLectivoInscripcion',
            'App\Listeners\EnviarNotificacionNuevoInscripcionComision',
        ],
        'App\Events\InscripcionComisionModificado' => [
            'App\Listeners\ActualizarComision',
            'App\Listeners\ActualizarPeriodoLectivoInscripcion',
        ],
        'App\Events\InscripcionMesaExamenMateriaNuevo' => [
            'App\Listeners\ActualizarMesaExamenMateria',
            'App\Listeners\ActualizarMetricaFinalInscripcion',
            'App\Listeners\EnviarNotificacionNuevoInscripcionMesaExamenMateria',
        ],
        'App\Events\InscripcionMesaExamenMateriaModificado' => [
            'App\Listeners\ActualizarMesaExamenMateria',
            'App\Listeners\ActualizarMetricaFinalInscripcion',
            //'App\Listeners\EnviarNotificacionNuevoInscripcionMesaExamenMateria',
        ],
        'App\Events\RegistracionAlumnoMateriaNota' => [
            'App\Listeners\ActualizarMetricaFinalInscripcion',
        ],

        'App\Events\DocenteModificado' => [
            'App\Listeners\ActualizarDocente',
        ],
        'App\Events\ComisionNuevo' => [
            'App\Listeners\ActualizarComision',
        ],
        'App\Events\ComisionModificado' => [
            'App\Listeners\ActualizarComision',
        ],
        'App\Events\ComisionExamenModificado' => [
            'App\Listeners\ActualizarComisionExamen',
        ],
        'App\Events\MateriaModificado' => [
            'App\Listeners\ActualizarMateria',
        ],
        'App\Events\MesaExamenMateriaModificado' => [
            'App\Listeners\ActualizarMesaExamenMateria',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
