<?php

// app/Http/Controllers/Doctor/ConsultationController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // <-- Import Rule for complex validation

class ConsultationController extends Controller
{
    /**
     * Display the consultation form for a specific visit token.
     * @param string $visit_token The unique token for the patient's current visit.
     */
    public function startConsultation(string $visit_token)
{
    // Fetch the visit along with patient and triage data
    // We allow fetching if the patient is waiting or if they are already being consulted.
    $visit = Visit::with(['patient', 'triage', 'consultation']) 
        ->where('visit_token', $visit_token)
        // Allow fetching if the visit is waiting ('Triage Completed')
        // OR if the doctor has already started/is continuing the consultation ('Consultation' / 'In Consultation')
        ->whereIn('status', ['Triage Completed', 'Consultation', 'In Consultation']) 
        ->firstOrFail();

    // Check if a consultation record already exists for this visit
    // We use the relationship for better Eloquent access
    $consultation = $visit->consultation ?? new Consultation(['visit_id' => $visit->id]);

    // *** REMOVE THE IMMEDIATE STATUS UPDATE ***
    // We will update the status only when the doctor *saves* the consultation.
    // if ($visit->status === 'Triage Completed') {
    //     $visit->update(['status' => 'Consultation', 'doctor_id' => auth()->id()]);
    // }
    
    // However, if you still want to track who is *viewing* the page, 
    // you can log the doctor_id on the consultation model here:
    if (!$consultation->doctor_id) {
        $consultation->doctor_id = auth()->id();
    }
    
    // Save the consultation model if it was just created (optional, but cleaner)
    if (!$consultation->exists) {
        $consultation->save();
    }

    // Assuming a view named 'outpatient.consultation.start' exists
    return view('outpatient.consultation.start', compact('visit', 'consultation'));
}
    /**
     * Store or update the consultation data and determine the next step.
     */
    public function storeOrUpdate(Request $request, Visit $visit)
    {
        // Define all valid next statuses after consultation
        $validNextSteps = ['Pharmacy', 'Lab/Rad', 'Inpatient', 'Discharged'];

        $request->validate([
            'diagnosis' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            // New validation for the next step
            'next_step' => ['required', 'string', Rule::in($validNextSteps)], 
        ]);

        $consultation = Consultation::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'doctor_id' => auth()->id(),
                'diagnosis' => $request->input('diagnosis'),
                'notes' => $request->input('notes'),
                'treatment_plan' => $request->input('treatment_plan'),
                'status' => 'Completed', // The consultation record itself is completed
            ]
        );
        
        // Update visit status based on the doctor's decision (next_step)
        $nextStatus = $request->input('next_step');

        if ($nextStatus === 'Discharged') {
            // 'Discharged' could map to 'Completed' in your main Visit status flow
            $visit->update(['status' => 'Completed']); 
            $message = 'Consultation completed. Patient discharged.';
        } else {
            // For Lab/Rad, Pharmacy, Inpatient: set the specific status
            $visit->update(['status' => $nextStatus]);
            $message = "Consultation recorded successfully and patient sent to **{$nextStatus}**.";
        }

        // Redirect the doctor back to their main queue/dashboard
        return redirect()->route('outpatient.dashboard', ['role' => 'doctor'])
                         ->with('success', $message);
    }

    /**
     * Placeholder method for viewing a completed consultation (for the Action button)
     */
    public function viewConsultation(string $visit_token)
    {
        $visit = Visit::with(['patient', 'triage', 'consultation.doctor'])
            ->where('visit_token', $visit_token)
            ->whereIn('status', [
            'Triage Completed',
            'In Consultation',
            'Consultation Completed',
            'Completed'
        ])
            ->orWhere('status', 'Completed') // For discharged patients
            ->firstOrFail();

        // Assuming a view named 'outpatient.consultation.view' exists
        return view('outpatient.consultation.view', compact('visit'));
    }
}
