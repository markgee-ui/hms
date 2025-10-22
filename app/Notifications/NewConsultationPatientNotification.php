<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewConsultationPatientNotification extends Notification
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
        // store directly to the database
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
            'title' => 'Patient Ready for Consultation',
            'message' => "Patient **{$patientName}** (Token: {$visitToken}) has completed Triage and is now waiting for Consultation.",
            'icon' => 'stethoscope',
            'link' => route('outpatient.dashboard', ['status' => 'Triage Completed']),
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}
