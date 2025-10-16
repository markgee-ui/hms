<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Consultation;
use App\Models\LabRequest; 
use App\Models\Prescription; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $nextStep = $request->input('next_step');
        $isLabRad = $nextStep === 'Lab/Rad';
        $isPharmacy = $nextStep === 'Pharmacy'; // NEW: Flag for Pharmacy

        $request->validate([
            'diagnosis' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            
            // NEW: Prescription Validation: Require if Pharmacy is selected
            'prescription' => ['nullable', 'string', Rule::requiredIf($isPharmacy)],

            // Lab/Rad Orders Validation: Require at least one order if Lab/Rad is selected.
            'lab_orders' => ['nullable', 'string', Rule::requiredIf($isLabRad && !$request->filled('radiology_orders'))],
            'radiology_orders' => ['nullable', 'string', Rule::requiredIf($isLabRad && !$request->filled('lab_orders'))],

            // Validation for the next step
            'next_step' => ['required', 'string', Rule::in($validNextSteps)], 
        ]);

        // 1. Update/Create the Consultation record (save the raw order/prescription text for history)
        $consultation = Consultation::updateOrCreate(
            ['visit_id' => $visit->id],
            [
                'doctor_id' => auth()->id(),
                'diagnosis' => $request->input('diagnosis'),
                'notes' => $request->input('notes'),
                'treatment_plan' => $request->input('treatment_plan'),
                'lab_orders' => $request->input('lab_orders'), 
                'radiology_orders' => $request->input('radiology_orders'),
                'prescription' => $request->input('prescription'), // NEW: Save prescription text to consultation record
                'status' => 'Completed', // The consultation record itself is completed
            ]
        );
        
        // 2. Handle LabRequest/Prescription creation based on next step

        // Handle Prescription creation if sending to Pharmacy
        if ($nextStep === 'Pharmacy') {
            $prescriptionData = trim($request->input('prescription') ?? '');
            
            if (!empty($prescriptionData)) {
                // Create a new Prescription record
                Prescription::create([
                    'visit_id' => $visit->id,
                    'doctor_id' => auth()->id(),
                    'patient_id' => $visit->patient->id, // Use patient ID from the visit relationship
                    'prescription_details' => $prescriptionData,
                    'status' => 'Pending', // Initial status for the pharmacy department
                ]);
            }
        }

        // Handle LabRequest creation if sending to Lab/Rad
        if ($nextStep === 'Lab/Rad') {
            $labOrders = trim($request->input('lab_orders') ?? '');
            $radOrders = trim($request->input('radiology_orders') ?? '');

            // Only create a request if the doctor actually provided orders
            if (!empty($labOrders) || !empty($radOrders)) {
                
                // Combine the text fields into a structured array for the 'tests_requested' field
                $testsRequested = [];
                if (!empty($labOrders)) {
                    $testsRequested['Laboratory'] = $labOrders;
                }
                if (!empty($radOrders)) {
                    $testsRequested['Radiology'] = $radOrders;
                }
                
                LabRequest::create([
                    'visit_id' => $visit->id,
                    'doctor_id' => auth()->id(),
                    // Use the combined array for the 'tests_requested' field
                    'tests_requested' => $testsRequested, 
                    'status' => 'Pending', // Initial status for the lab/rad department
                ]);
            }
        }

        // 3. Update visit status based on the doctor's decision (next_step)
        if ($nextStep === 'Discharged') {
            // 'Discharged' could map to 'Completed' in your main Visit status flow
            $visit->update(['status' => 'Completed']); 
            $message = 'Consultation completed. Patient discharged.';
        } else {
            // For Lab/Rad, Pharmacy, Inpatient: set the specific status
            $visit->update(['status' => $nextStep]);
            $message = "Consultation recorded successfully and patient sent to **{$nextStep}**.";
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
    
    // Existing methods follow...

    public function laboratoryQueue()
    {
        // Fetch visits that have been sent to Lab/Rad
        $visits = Visit::with(['patient', 'labRequests.doctor'])
            ->where('status', 'Lab/Rad Results Ready')
            ->latest()
            ->get();

        return view('outpatient.consultation.laboratory_queue', compact('visits'));
    }
    
    public function reviewResults($visit_token)
    {
        // Load visit + related lab results
        $visit = Visit::with(['patient', 'labRequests'])->where('visit_token', $visit_token)->firstOrFail();

        return view('outpatient.consultation.review_results', compact('visit'));
    }

    public function storePrescription(Request $request, $visit_token)
    {
        // NOTE: This method is now redundant if the Prescription is stored in storeOrUpdate, 
        // but it is retained here as it was in the original file.
        $visit = Visit::where('visit_token', $visit_token)->firstOrFail();

        $request->validate([
            'prescription_data' => 'required|string',
        ]);

        // Store prescription (linked to visit + doctor)
        Prescription::create([
        'visit_id' => $visit->id,
        'doctor_id' => auth()->id(),
        'patient_id' => $visit->patient_id,
        'prescription_details' => $request->input('prescription_data'),
        'status' => 'Pending',
    ]);

        // Move the visit to the Pharmacy Queue
        $visit->update(['status' => 'Pharmacy']);

        return redirect()->route('outpatient.dashboard', ['role' => 'doctor'])
                            ->with('success', 'Prescription added successfully. Patient moved to Pharmacy Queue.');
    }
    
    public function prescriptionHistory()
    {
        // Only show prescriptions created by the logged-in doctor
        $prescriptions = Prescription::with(['patient', 'visit'])
            ->where('doctor_id', auth()->id())
            ->latest()
            ->get();

        return view('outpatient.consultation.prescription', compact('prescriptions'));
    }
}