<?php

namespace App\Notifications;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

// i will remove the shouldqueue interface in production if not needed

class NewTriagePatientNotification extends Notification implements ShouldQueue
{ //when a channel needs to make an external api call to deliver the notificastion
    //to speed up apk rt,i use shouldqueue interface and queueable trait.
    use Queueable;

    protected $visit;

    /**
     * Create a new notification instance.
     *
     * @param Visit $visit The visit model being moved to triage.
     */
    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // We will store this notification in the database
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        $patientName = $this->visit->patient->name ?? 'Unknown Patient';
        $visitToken = $this->visit->visit_token;

        return [
            'title' => 'New Patient in Triage Queue',
            'message' => "Patient **{$patientName}** (Token: {$visitToken}) has been added to the Triage queue.",
            'icon' => 'user-plus', 
            'link' => route('outpatient.dashboard', ['status' => 'Registered']),
            'patient_id' => $this->visit->patient_id,
            'visit_token' => $visitToken,
        ];
    }
}