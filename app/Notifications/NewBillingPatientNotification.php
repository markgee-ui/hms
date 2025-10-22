<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBillingPatientNotification extends Notification
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
            'title' => 'Patient Ready for Billing',
            'message' => "Patient **{$patientName}** (Token: {$visitToken}) is waiting for final billing/payment.",
            'icon' => 'cash-register', // Icon for cashier/billing
            'link' => route('outpatient.dashboard', ['status' => 'Billing']), 
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}
