<?php

namespace App\Notifications;

use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalWebButton;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MesaExamenDocenteAsistencia extends Notification
{
    use Queueable;
    private $id_mesa_examen_materia;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($id_mesa_examen_materia)
    {
        private $id_mesa_examen_materia;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [OneSignalChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return OneSignalMessage::create()
            ->subject("Your {$notifiable->service} account was approved!")
            ->body("Click here to see details.")
            ->url('http://onesignal.com')
            ->webButton(
                OneSignalWebButton::create('link-1')
                    ->text('Click here')
                    ->icon('https://upload.wikimedia.org/wikipedia/commons/4/4f/Laravel_logo.png')
                    ->url('http://laravel.com')
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
