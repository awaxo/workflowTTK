<?php

namespace App\Notifications;

use App\Models\Room;
use App\Models\Workgroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * RoomAssignmentNotification is a notification class that sends an email
 * to the workgroup admin users when a workgroup is closed and its rooms
 * need to be reassigned to another group.
 * It includes the details of the workgroup and the associated rooms.
 */
class RoomAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The deleted workgroup
     *
     * @var Workgroup
     */
    protected $workgroup;

    /**
     * Rooms associated with the workgroup
     * 
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $rooms;

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
     * @param array $ccEmails
     * @return void
     */
    public function __construct(Workgroup $workgroup, array $ccEmails = [])
    {
        $this->workgroup = $workgroup;
        $this->ccEmails = $ccEmails;
        
        // Lekérdezzük a csoporthoz tartozó helyiségeket
        $this->rooms = Room::where('workgroup_number', $workgroup->workgroup_number)->get();
        Log::info("RoomAssignmentNotification: {$workgroup->workgroup_number} - {$workgroup->name} csoporthoz tartozó helyiségek lekérdezve");
        Log::info($this->rooms->toArray());
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
            ->subject('Helyiség tulajdonjog átadás szükséges')
            ->greeting('Tisztelt ' . $notifiable->name . '!')
            ->line('A(z) ' . $this->workgroup->workgroup_number . ' - ' . $this->workgroup->name . ' csoport lezárásra került.');
        
        if ($this->rooms->isEmpty()) {
            $mailMessage->line('A csoporthoz nem tartoznak helyiségek a nyilvántartásban.');
        } else {
            $mailMessage->line('A csoporthoz az alábbi helyiségek tartoznak, amelyeket másik csoporthoz szükséges tulajdonosként hozzárendelni:')
                ->line('');

            // Add each room to the email
            foreach ($this->rooms as $room) {
                $mailMessage->line("- {$room->room_number}");
            }
        }

        $mailMessage
            ->line('')
            ->line('Kérjük, hogy a terület nyilvántartási adatbázisban a megfelelő módosításokat szíveskedjenek végrehajtani.')
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
            ],
            'rooms' => $this->rooms->map(function ($room) {
                return [
                    'room_number' => $room->room_number
                ];
            })->toArray()
        ];
    }
}