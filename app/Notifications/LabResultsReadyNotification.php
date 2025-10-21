<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LabResultsReadyNotification extends Notification
{
    use Queueable;

    protected $visit;

    /**
     * Create a new notification instance.
     * The notification carries the Visit model to access patient/visit details.
     */
    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        $patientName = $this->visit->patient->name ?? 'Unknown Patient';
        $visitToken  = $this->visit->visit_token;

        return [
            'title' => 'Lab Results Ready for Review',
            'message' => "The Lab/Rad results for patient **{$patientName}** (Token: {$visitToken}) are ready for your review.",
            'icon' => 'microscope', // Icon for lab results
            // Assuming a route exists for doctors to review results, perhaps based on visit ID
            'link' => route('outpatient.dashboard', ['visit' => $this->visit->id]), 
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}
