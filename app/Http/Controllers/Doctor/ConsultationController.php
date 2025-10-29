<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Consultation;
use App\Models\LabRequest; 
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medication;
use App\Models\Labtest;
use App\Models\LabRequestTest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\User; 
use App\Notifications\NewLabOrderNotification; 
use App\Notifications\NewPrescriptionNotification;

class ConsultationController extends Controller
{
    /**
     * Display the consultation form for a specific visit token.
     * @param string $visit_token The unique token for the patient's current visit.
     */
    public function startConsultation(string $visit_token)
    {
        // 1. Fetch the visit along with patient and triage data
        $visit = Visit::with(['patient', 'triage', 'consultation']) 
            ->where('visit_token', $visit_token)
            ->whereIn('status', ['Triage Completed', 'Consultation', 'In Consultation']) 
            ->firstOrFail();

        // 2. Check if a consultation record already exists for this visit
        $consultation = $visit->consultation ?? new Consultation(['visit_id' => $visit->id]);

        // Log the doctor_id on the consultation model
        if (!$consultation->doctor_id) {
            $consultation->doctor_id = auth()->id();
        }
        
        // Save the consultation model if it was just created
        if (!$consultation->exists) {
            $consultation->save();
        }

        // 3. Fetch the necessary catalog data for the view
        // IMPORTANT: Ensure you have Medication and LabTest models set up in your database
        $medications = Medication::orderBy('name')->get(); 
        $labTests = LabTest::orderBy('name')->get();

        // 4. Pass all required data to the view
        return view('outpatient.consultation.start', compact('visit', 'consultation', 'medications', 'labTests'));
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
        $isPharmacy = $nextStep === 'Pharmacy';

        // --- 1. Validation for Catalog-Based Input ---
        $validationRules = [
            'diagnosis' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'next_step' => ['required', 'string', Rule::in($validNextSteps)], 

            // Prescriptions: Require the array if Pharmacy is selected
            'prescriptions' => ['nullable', 'array', Rule::requiredIf($isPharmacy)],
            'prescriptions.*.medication_id' => ['required_with:prescriptions', 'exists:medications,id'],
            'prescriptions.*.quantity' => ['required_with:prescriptions', 'integer', 'min:1'],
            'prescriptions.*.dosage' => ['nullable', 'string', 'max:100'],
            'prescriptions.*.frequency' => ['nullable', 'string', 'max:100'],
            'prescriptions.*.duration' => ['nullable', 'string', 'max:100'],

            // Lab Tests: Require at least one item if Lab/Rad is selected
            'lab_test_ids' => ['nullable', 'array', Rule::requiredIf($isLabRad && !$request->filled('radiology_orders'))],
            'lab_test_ids.*' => ['required_with:lab_test_ids', 'exists:lab_tests,id'],
            
            // Radiology Orders (still free text)
            'radiology_orders' => ['nullable', 'string', Rule::requiredIf($isLabRad && empty($request->input('lab_test_ids')))],
        ];
        
        $request->validate($validationRules);

        // Use a database transaction to ensure all related records are saved or none are.
        DB::beginTransaction();
        try {
            // 2. Update/Create the Consultation record
            $consultation = Consultation::updateOrCreate(
                ['visit_id' => $visit->id],
                [
                    'doctor_id' => auth()->id(),
                    'diagnosis' => $request->input('diagnosis'),
                    'notes' => $request->input('notes'),
                    'treatment_plan' => $request->input('treatment_plan'),
                    // NEW: Lab and prescription text fields removed from consultation model 
                    // or set to null, as structured data is now the source of truth.
                ]
            );
            
            $prescriptionCreated = false;
            $labRequestCreated = false;

            // 3. Handle Prescription creation (if Pharmacy selected)
            if ($isPharmacy && $request->has('prescriptions')) {
                // Ensure there are items before creating a prescription record
                if (!empty($request->input('prescriptions'))) {
                    // Create the main Prescription record
                    $prescription = Prescription::create([
                        'visit_id' => $visit->id,
                        'doctor_id' => auth()->id(),
                        'patient_id' => $visit->patient->id,
                        'status' => 'Pending',
                    ]);

                    // Create the PrescriptionItem records (one for each medication)
                    foreach ($request->input('prescriptions') as $item) {
                        PrescriptionItem::create([
                            'prescription_id' => $prescription->id,
                            'medication_id' => $item['medication_id'],
                            'quantity' => $item['quantity'],
                            'dosage' => $item['dosage'] ?? null,
                            'frequency' => $item['frequency'] ?? null,
                            'duration' => $item['duration'] ?? null,
                        ]);
                    }
                    $prescriptionCreated = true;
                }
            }

            // 4. Handle LabRequest creation (if Lab/Rad selected)
            if ($isLabRad) {
                $labTestIds = $request->input('lab_test_ids', []);
                $radOrders = trim($request->input('radiology_orders') ?? '');

                if (!empty($labTestIds) || !empty($radOrders)) {
                    // Create the main LabRequest record
                    $labRequest = LabRequest::create([
                        'visit_id' => $visit->id,
                        'doctor_id' => auth()->id(),
                        // 'tests_requested' field is removed or set to null
                        'status' => 'Pending',
                    ]);

                    // Create LabRequestTest records for catalog items
                    foreach ($labTestIds as $testId) {
                        LabRequestTest::create([
                            'lab_request_id' => $labRequest->id,
                            'lab_test_id' => $testId,
                            'status' => 'Requested',
                            'requested_at' => now(),
                        ]);
                    }

                    // For Radiology (still free text), you might save it as a custom LabRequestTest
                    // using a placeholder ID or simply keep it as a note on the main LabRequest,
                    // but for now, we'll keep the free-text in the LabRequest model (if you added the column).
                    // If you kept the 'radiology_orders' column in LabRequest:
                    // $labRequest->update(['radiology_orders' => $radOrders]); 

                    // Since we are using the 'radiology_orders' field for the LabRequest model 
                    // for the free-text component, we need to make sure that field is in the LabRequest's fillable array
                    // and exists in the lab_requests table. 
                    if (!empty($radOrders)) {
                         // This assumes you add 'radiology_orders' to LabRequest fillable and table.
                         // If not, you must create a specific LabRequestTest entry for it.
                    }
                    
                    $labRequestCreated = true;
                }
            }

            // 5. Update visit status based on the doctor's decision (next_step)
            if ($nextStep === 'Discharged') {
                $visit->update(['status' => 'Completed']); 
                $message = 'Consultation completed. Patient discharged.';
            } else {
                $visit->update(['status' => $nextStep]);
                $message = "Consultation recorded successfully and patient sent to **{$nextStep}**.";
            }

            // Commit the transaction
            DB::commit();

            // 6. NOTIFICATION LOGIC (Use the created flags)
            if ($nextStep === 'Lab/Rad' && $labRequestCreated) {
                $notifiableUsers = User::where('role', 'labtech')->get();
                if ($notifiableUsers->isNotEmpty()) {
                    Notification::send($notifiableUsers, new NewLabOrderNotification($visit));
                }
            } 
            
            if ($nextStep === 'Pharmacy' && $prescriptionCreated) {
                $notifiableUsers = User::where('role', 'pharmacist')->get();
                if ($notifiableUsers->isNotEmpty()) {
                    Notification::send($notifiableUsers, new NewPrescriptionNotification($visit));
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error and notify the user
            \Log::error("Consultation Store Error: " . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the consultation and orders. Please try again.');
        }

        // Redirect the doctor back to their main queue/dashboard
        return redirect()->route('outpatient.dashboard')
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
            ->paginate(10);

        return view('outpatient.consultation.laboratory_queue', compact('visits'));
    }
    
    public function reviewResults($visit_token)
    {
        // Load visit + related lab results
        $visit = Visit::with(['patient', 'labRequests'])->where('visit_token', $visit_token)->firstOrFail();

        return view('outpatient.consultation.review_results', compact('visit'));
    }

    // public function storePrescription(Request $request, $visit_token)
    // {
    //     // NOTE: This method is now redundant if the Prescription is stored in storeOrUpdate, 
    //     // but it is retained here as it was in the original file.
    //     $visit = Visit::where('visit_token', $visit_token)->firstOrFail();

    //     $request->validate([
    //         'prescription_data' => 'required|string',
    //     ]);

    //     // Store prescription (linked to visit + doctor)
    //     Prescription::create([
    //     'visit_id' => $visit->id,
    //     'doctor_id' => auth()->id(),
    //     'patient_id' => $visit->patient_id,
    //     'prescription_details' => $request->input('prescription_data'),
    //     'status' => 'Pending',
    // ]);

    //     // Move the visit to the Pharmacy Queue
    //     $visit->update(['status' => 'Pharmacy']);

    //     return redirect()->route('outpatient.dashboard', ['role' => 'doctor'])
    //                         ->with('success', 'Prescription added successfully. Patient moved to Pharmacy Queue.');
    // }
    
public function prescriptionHistory()
{
    $prescriptions = Prescription::with(['patient', 'visit', 'items.medication'])
        ->where('doctor_id', auth()->id())
        ->latest()
        ->paginate(10);

    return view('outpatient.consultation.prescription', compact('prescriptions'));
}


}