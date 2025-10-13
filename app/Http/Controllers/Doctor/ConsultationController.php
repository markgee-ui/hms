<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display the consultation form for a specific visit token.
     * @param string $visit_token The unique token for the patient's current visit.
     */
    public function startConsultation(string $visit_token)
    {
        // Fetch the visit along with patient and triage data
        $visit = Visit::with(['patient', 'triage'])
            ->where('visit_token', $visit_token)
            ->where('status', 'Triage Completed')
            ->firstOrFail();

        // Check if a consultation record already exists for this visit
        $consultation = $visit->consultation ?? new Consultation(['visit_id' => $visit->id]);

        // Update the visit status to 'In Consultation' (optional: for real-time tracking)
        // Note: We might want to move this to a dedicated endpoint if the app is heavily real-time
        $visit->update(['status' => 'In Consultation']);

        // Assuming a view named 'outpatient.consultation.start' exists
        return view('outpatient.consultation.start', compact('visit', 'consultation'));
    }

    /**
     * Store or update the consultation data.
     */
    public function storeOrUpdate(Request $request, Visit $visit)
    {
        $request->validate([
            'diagnosis' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
        ]);

        $consultation = Consultation::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'doctor_id' => auth()->id(),
                'diagnosis' => $request->input('diagnosis'),
                'notes' => $request->input('notes'),
                'treatment_plan' => $request->input('treatment_plan'),
                'status' => 'Completed',
            ]
        );
        
        // Update visit status to the next step (e.g., 'Consultation Completed', or 'Pending Lab/Pharmacy')
        $visit->update(['status' => 'Consultation Completed']);

        return redirect()->route('outpatient.dashboard')
                         ->with('success', 'Consultation recorded successfully and patient sent to the next step.');
    }
}
