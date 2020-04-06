<?php

namespace App\Notifications;

use App\Models\Notificacion;

use NotificationChannels\ExpoPushNotifications\ExpoChannel;
use NotificationChannels\ExpoPushNotifications\ExpoMessage;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class NotificacionNueva extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $notificacion;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Notificacion $notificacion)
    {
        $this->notificacion = $notificacion;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [ExpoChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toExpoPush($notifiable)
    {
        return ExpoMessage::create()
            ->title($this->notificacion->nombre)
            ->body($this->notificacion->descripcion);
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
