<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Triage;
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
        // Find the visit by token
        $visit = Visit::where('visit_token', $token)
                      ->where('status', 'Registered') // Only move visits that are freshly registered
                      ->first();

        if (!$visit) {
            return back()->with('error', 'Error: Visit not found or already queued.');
        }

        try {
            // Update status: Move to the Triage Queue for the Nurse
            $visit->status = 'Waiting for Triage';
            $visit->triage_queue_time = now(); // Timestamp for tracking queue time
            $visit->save();

            // Redirect back to the receptionist's dashboard
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
}
