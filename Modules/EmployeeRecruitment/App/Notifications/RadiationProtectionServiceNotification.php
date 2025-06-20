<?php

namespace Modules\EmployeeRecruitment\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\EmployeeRecruitment\App\Models\RecruitmentWorkflow;

/**
 * Class RadiationProtectionServiceNotification
 * This notification is sent to users when a new recruitment workflow is created,
 * specifically for cases involving ionizing radiation risk.
 * It extends the base Notification class and uses the Queueable trait for queueing.
 */
class RadiationProtectionServiceNotification extends Notification
{
    use Queueable;

    public $workflow;

    /**
     * Create a new notification instance.
     *
     * @param RecruitmentWorkflow $workflow
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
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('https://ugyintezes.ttk.hu/folyamat/megtekintes/' . $this->workflow->id);

        return (new MailMessage)
                    ->subject('Értesítés új belépési folyamatról - Ionizáló sugárzás kockázat')
                    ->greeting('Tisztelt ' . $notifiable->name . '!')
                    ->line('')
                    ->line('Az alábbi felvétel van folyamatban, amely ionizáló sugárzás kockázattal jár:')
                    ->line('Név: ' . $this->workflow->name)
                    ->line('Csoport 1: ' . ($this->workflow->workgroup1->name ?? '') . ' (' . ($this->workflow->workgroup1->workgroup_number ?? '') . ')')
                    ->line('Csoport 2: ' . ($this->workflow->workgroup2->name ?? '') . ' (' . ($this->workflow->workgroup2->workgroup_number ?? '') . ')')
                    ->line('Jogviszony kezdete: ' . $this->workflow->employment_start_date)
                    ->line('Jogviszony vége: ' . $this->workflow->employment_end_date)
                    ->line('Ionizáló sugárzási kitettség: ' . $this->getRadiationExposureLevel($this->workflow))
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

    /**
     * Get radiation exposure level text based on the value.
     *
     * @param RecruitmentWorkflow $workflow
     * @return string
     */
    private function getRadiationExposureLevel(RecruitmentWorkflow $workflow): string
    {
        $medicalData = json_decode($workflow->medical_eligibility_data, true);
        
        if (isset($medicalData['ionizing_radiation_exposure'])) {
            switch ($medicalData['ionizing_radiation_exposure']) {
                case 'resz':
                    return 'A munkaidő egy részében';
                case 'egesz':
                    return 'A munkaidő egészében';
                default:
                    return 'Nem meghatározott';
            }
        }
        
        return 'Nem meghatározott';
    }
}