<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Triage;
use App\Models\User;
use App\Notifications\NewTriagePatientNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TriageController extends Controller
{
    /**
     * [RECEPTIONIST ACTION]
     * Updates the visit status from 'Registered' to 'Waiting for Triage'.
     * This puts the patient into the Nurse's queue.
     * @param string $token The visit token
     */
    public function sendToTriageQueue($token)
{
    $visit = Visit::where('visit_token', $token)
                  ->where('status', 'Registered')
                  ->first();

    if (!$visit) {
        return back()->with('error', 'Error: Visit not found or already queued.');
    }

    try {
        $visit->status = 'Waiting for Triage';
        $visit->triage_queue_time = now();
        $visit->save();

        // Notify all nurses
        $nurses = User::where('role', 'nurse')->get();
        foreach ($nurses as $nurse) {
            $nurse->notify(new NewTriagePatientNotification($visit));
        }

        return redirect()->route('outpatient.dashboard')->with('success', 
            "Patient {$visit->patient->name} (Token: {$token}) successfully sent to the Triage Queue."
        );

    } catch (\Exception $e) {
        return back()->with('error', 'Failed to update visit status: ' . $e->getMessage());
    }
}



    /**
     * [NURSE ACTION]
     * Show the Triage form for a specific visit.
     * * NOTE: Status check changed from 'Registered' to 'Waiting for Triage' 
     * to pick up patients sent by the receptionist.
     * @param string $visit_token
     */
    public function startTriage($visit_token)
    {
        // Find the visit and ensure it's in the 'Waiting for Triage' status
        $visit = Visit::with('patient')
            ->where('visit_token', $visit_token)
            ->where('status', 'Waiting for Triage') // Only allow nurses to start triage if receptionist queued it
            ->firstOrFail();

        // Check if triage already exists (optional safety check)
        if ($visit->triage) {
            return redirect()->route('outpatient.dashboard')->with('warning', 'Triage already completed for this visit.');
        }

        return view('outpatient.triage.form', compact('visit'));
    }

    /**
     * Store the Triage details and update the visit status.
     */
    public function storeTriage(Request $request, $visit_id)
    {
        $validated = $request->validate([
            'bp' => 'required|string|max:15',
            'temperature' => 'required|numeric|min:35.0|max:43.0',
            'pulse' => 'required|integer|min:30|max:200',
            'weight' => 'required|numeric|min:1.0|max:500.0',
            'chief_complaint' => 'required|string|max:255',
            'symptoms' => 'nullable|string',
            'triage_category' => 'nullable|string|max:50', // Optional triage category
        ]);

        try {
            DB::beginTransaction();

            $visit = Visit::findOrFail($visit_id);

            // 1. Record Triage Details
            Triage::create([
                'visit_id' => $visit->id,
                'nurse_id' => Auth::id(),
                'bp' => $validated['bp'],
                'temperature' => $validated['temperature'],
                'pulse' => $validated['pulse'],
                'weight' => $validated['weight'],
                'chief_complaint' => $validated['chief_complaint'],
                'symptoms' => $validated['symptoms'],
                'triage_category' => $validated['triage_category'] ?? null, // Store triage category if provided
            ]);

            // 2. Update Visit Status to ready for Consultation
            $visit->update(['status' => 'Triage Completed']); // Changed to 'Triage Completed' for clarity

            DB::commit();

            // Redirect nurse back to their dashboard queue
            return redirect()->route('outpatient.dashboard')->with('success', 
                "Vitals for Patient {$visit->patient->name} recorded. Patient sent to Consultation Queue."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save triage data: ' . $e->getMessage());
        }
    }
     public function viewTriage($visit_token)
    {
        // Eager load the patient details and the associated triage record
        $visit = Visit::with(['patient', 'triage'])
            ->where('visit_token', $visit_token)
            ->firstOrFail();

        // Ensure the triage is actually completed before showing the clinical data view
        if ($visit->status !== 'Triage Completed' && $visit->status !== 'Consultation Started' && $visit->status !== 'Consultation Completed') {
             // Redirect or show a message if the data is not ready, 
             // but since the link only appears when Triage is completed, this is mainly a safeguard.
             return redirect()->route('outpatient.dashboard')->with('error', 'Triage record for this visit is not yet finalized.');
        }

        // Render the dedicated clinical review view
        return view('outpatient.triage.view', compact('visit'));
    }
     /**
     * Display the triage assessment form for editing.
     *
     * @param string $visit_token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(string $visit_token)
    {
        // 1. Find the Triage record associated with the visit token.
        // Eager load 'visit' and 'visit.patient' to access patient details in the view.
        $triage = Triage::whereHas('visit', function ($query) use ($visit_token) {
            $query->where('visit_token', $visit_token);
        })
        ->with('visit.patient')
        ->first();

        // 2. Handle case where the triage record is not found.
        if (!$triage) {
            // Use a flash message or log, then redirect back to the queue
            session()->flash('error', "Triage assessment for token '{$visit_token}' not found.");
            return redirect()->route('outpatient.dashboard', ['role' => 'nurse']);
        }

        // 3. Return the edit view with the triage data.
        return view('outpatient.triage.edit', compact('triage'));
    }

    /**
     * Update the specified triage assessment in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $visit_token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $visit_token)
    {
        // 1. Validation for the  set of inputs
        $validatedData = $request->validate([
            'bp' => ['required', 'string', 'regex:/^\d{2,3}\/\d{2,3}$/'], 
            'temperature' => ['required', 'numeric', 'min:35.0', 'max:42.0'],
            'pulse' => ['required', 'integer', 'min:30', 'max:250'], 
            'weight' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'chief_complaint' => ['required', 'string', 'max:500'],
            'symptoms' => ['nullable', 'string', 'max:1000'],
        ]);

        // 2. Find the Triage record to update
        $triage = Triage::whereHas('visit', function ($query) use ($visit_token) {
            $query->where('visit_token', $visit_token);
        })->with('visit.patient')->first();

        if (!$triage) {
            session()->flash('error', "Triage record for token '{$visit_token}' could not be updated (Record not found).");
            return redirect()->route('outpatient.dashboard', ['role' => 'nurse']);
        }

        // --- Data Mapping (Directly matches the Triage Model fields) ---
        $updateData = [
            'bp' => $validatedData['bp'], 
            'temperature' => $validatedData['temperature'],
            'pulse' => $validatedData['pulse'], 
            'weight' => $validatedData['weight'],
            'chief_complaint' => $validatedData['chief_complaint'],
            'symptoms' => $validatedData['symptoms'], 

            // NOTE: If you need to store the nurse's general notes separately from symptoms, 
            // you might need to add 'nurse_notes' back to your Triage model.
            // Since it's not in the model's $fillable array, we use 'symptoms' for the details.
        ];

        // 3. Update the Triage record with the mapped data
        $triage->update($updateData);

        // 4. Redirect with a success message
        session()->flash('success', "Triage assessment for patient '{$triage->visit->patient->name}' has been successfully updated.");
        return redirect()->route('outpatient.dashboard', ['role' => 'nurse']);
    }
}
