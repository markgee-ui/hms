<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    /**
     * Show the patient search and registration form.
     */
    public function showRegistrationForm()
    {
        return view('outpatient.receptionist.registration');
    }

    /**
     * Search for an existing patient by ID, phone, or national ID.
     */
    public function searchPatient(Request $request)
    {
        $request->validate(['search_term' => 'required|string']);
        $term = $request->input('search_term');

        $patient = Patient::where('national_id', $term)
                          ->orWhere('phone', $term)
                          ->orWhere('patient_id', $term)
                          ->first();

        if ($patient) {
            return view('outpatient.receptionist.registration', ['patient' => $patient, 'search_success' => true]);
        }
        
        return redirect()->route('outpatient.register')->with(['search_term' => $term, 'search_fail' => true]);
    }

    /**
     * Store a new patient or create a new visit for an existing one.
     */
    public function storePatientAndVisit(Request $request)
    {
        if ($request->has('existing_patient_id')) {
            $patient = Patient::findOrFail($request->input('existing_patient_id'));
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:patients,phone|max:15',
                'national_id' => 'nullable|string|unique:patients,national_id|max:20',
                'age' => 'required|integer|min:0',
                'gender' => 'required|in:Male,Female,Other',
                'address' => 'nullable|string',
                'next_of_kin' => 'nullable|string|max:255',
            ]);
            
            $patient = Patient::firstOrCreate(
                ['national_id' => $validated['national_id'] ?? null],
                array_merge($validated, [
                    'patient_id' => $this->generateUniquePatientId(), 
                ])
            );
        }

        try {
            DB::beginTransaction();

            $visit = Visit::create([
                'patient_id' => $patient->id,
                'visit_type' => 'Outpatient',
                'visit_token' => $this->generateVisitToken(),
                'status' => 'Registered', // Next: Triage
                'registration_date' => now(),
            ]);

            DB::commit();
            
            // CORRECTED REDIRECTION to the defined dashboard route
            return redirect()->route('outpatient.dashboard')->with('success', 
                "Patient {$patient->name} registered with Visit Token: {$visit->visit_token}. Next: Triage."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified patient.
     * * @param int $id The patient ID
     */
    public function edit($id)
    {
        $patient = Patient::findOrFail($id);
        return view('outpatient.receptionist.edit', compact('patient'));
    }

    /**
     * Update the specified patient in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The patient ID
     */
    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Rule::unique ignores the current patient's existing value for phone/national_id
            'phone' => ['required', 'string', 'max:15', Rule::unique('patients', 'phone')->ignore($patient->id)],
            'national_id' => ['nullable', 'string', 'max:20', Rule::unique('patients', 'national_id')->ignore($patient->id)],
            'age' => 'required|integer|min:0',
            'gender' => 'required|in:Male,Female,Other',
            'address' => 'nullable|string',
            'next_of_kin' => 'nullable|string|max:255',
        ]);

        $patient->update($validated);

        return redirect()->route('outpatient.dashboard')->with('success', 
            "Patient record for {$patient->name} ({$patient->patient_id}) updated successfully."
        );
    }

    private function generateUniquePatientId() {
        return 'OP-' . date('ym') . '-' . str_pad(Patient::count() + 1, 5, '0', STR_PAD_LEFT);
    }

    private function generateVisitToken() {
        return str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT); 
    }
}
