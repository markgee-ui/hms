<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLabOrderNotification extends Notification
{
    use Queueable;

    protected $visit;

    /**
     * Create a new notification instance.
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
            'title' => 'New Lab/Radiology Request',
            'message' => "Patient **{$patientName}** (Token: {$visitToken}) is waiting for testing at the Lab/Rad department.",
            'icon' => 'vial', // Font Awesome icon for lab
            'link' => route('outpatient.dashboard', ['status' => 'Lab/Rad']), // Assuming you have a route for the lab tech queue
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}
