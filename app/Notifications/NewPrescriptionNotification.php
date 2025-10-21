<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPrescriptionNotification extends Notification
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
            'title' => 'New Prescription Order',
            'message' => "Patient **{$patientName}** (Token: {$visitToken}) is waiting for prescription fulfillment at the Pharmacy.",
            'icon' => 'pills', // Font Awesome icon for medication
            'link' => route('outpatient.dashboard', ['status' => 'Pharmacy']), // Assuming you have a route for the pharmacist queue
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}
