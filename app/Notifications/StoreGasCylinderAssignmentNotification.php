<?php

namespace App\Notifications;

use App\Models\Workgroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * StoreGasCylinderAssignmentNotification is a notification class that sends an email
 * to the warehouse when a workgroup is closed, requesting the assignment of gas cylinders
 * to another group.
 */
class StoreGasCylinderAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted workgroup
     *
     * @var Workgroup
     */
    protected $workgroup;

    /**
     * To email address
     *
     * @var string
     */
    protected $toEmail;

    /**
     * Carbon copy emails
     *
     * @var array
     */
    protected $ccEmails;

    /**
     * Create a new notification instance.
     *
     * @param Workgroup $workgroup
     * @param string $toEmail
     * @param array $ccEmails
     * @return void
     */
    public function __construct(Workgroup $workgroup, string $toEmail, array $ccEmails = [])
    {
        $this->workgroup = $workgroup;
        $this->toEmail = $toEmail;
        $this->ccEmails = $ccEmails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('Gázpalack tulajdonjog átadás szükséges')
            ->greeting('Tisztelt Raktár!')
            ->line('A(z) ' . $this->workgroup->workgroup_number . ' - ' . $this->workgroup->name . ' csoport lezárásra került.')
            ->line('Kérjük, hogy a terület nyilvántartási adatbázisban a lezárt csoporthoz tartozó gázpalackokat másik csoporthoz szíveskedjenek tulajdonosként hozzárendelni.')
            ->line('')
            ->line('Üdvözlettel,')
            ->line('Ügyintézési rendszer');

        // Add CC emails
        foreach ($this->ccEmails as $email) {
            $mailMessage->cc($email);
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'workgroup' => [
                'id' => $this->workgroup->id,
                'number' => $this->workgroup->workgroup_number,
                'name' => $this->workgroup->name
            ]
        ];
    }
    
    /**
     * Külső email címzett meghatározása.
     * Ez a metódus felülírja az alapértelmezett címzettet.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function routeNotificationForMail($notifiable)
    {
        // A külső email címet és nevet adjuk vissza, nem a felhasználóét
        return [$this->toEmail => 'Raktár'];
    }
}