<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use App\Models\AbstractWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

class EntryPermissionNotification extends Notification
{
    use Queueable;

    public $workflow;

    /**
     * Create a new notification instance.
     */
    public function __construct(RecruitmentWorkflow $workflow)
    {
        $this->workflow = $workflow;
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
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);
        
        $entryPermissions = '-';
        if ($this->workflow->entry_permissions) {
            $entriesArray = explode(',', $this->workflow->entry_permissions);
            $translatedEntries = array_map(function($entry) {
                $translation = trans('entries.' . $entry);
                return $translation === 'entries.' . $entry ? $entry : $translation;
            }, $entriesArray);
            $entryPermissions = implode(', ', $translatedEntries);
        }

        return (new MailMessage)
                    ->subject('Értesítés belépő belépési jogosultságairól')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line('Az alábbi ügyet a kötelezettségvállaló jóváhagyta:')
                    ->line('Név: ' . $this->workflow->name)
                    ->line('Volt már munkajogviszonya a Kutatóközponttal: ' . ($this->workflow->has_prior_employment == 1 ? 'Igen' : 'Nem'))
                    ->line('Jelenleg van önkéntes szerződéses jogviszonya a Kutatóközponttal: ' . ($this->workflow->has_current_volunteer_contract == 1 ? 'Igen' : 'Nem'))
                    ->line('Állampolgárság: ' . $this->workflow->citizenship)
                    ->line('Csoport 1: ' . $this->workflow->workgroup1->name . ' (' . $this->workflow->workgroup1->workgroup_number . ')')
                    ->line('Csoport 2: ' . $this->workflow->workgroup2->name . ' (' . $this->workflow->workgroup2->workgroup_number . ')')
                    ->line('Jogviszony kezdete: ' . $this->workflow->employment_start_date)
                    ->line('Jogviszony vége: ' . $this->workflow->employment_end_date)
                    ->line('Javasolt email cím: ' . $this->workflow->email)
                    ->line('Belépési jogosultságok: ' . $entryPermissions)
                    ->line('Rendszám: ' . $this->workflow->license_plate)
                    ->line('Dolgozószoba: ' . $this->workflow->employee_room)
                    ->line('Telefon mellék: ' . $this->workflow->phone_extension)
                    ->line('TAJ szám: ' . preg_replace('/(\d{3}) \d{3} (\d{3})/', '$1 XXX $2', $this->workflow->social_security_number))
                    ->line('')
                    ->action('Ügy megtekintése', $url)
                    ->line('')
                    ->line('Üdvözlettel,')
                    ->line('Ügyintézési rendszer');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
