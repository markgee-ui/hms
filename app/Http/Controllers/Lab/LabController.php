<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\LabRequest;
use Illuminate\Http\Request;

class LabController extends Controller
{
    /**
     * Display the main lab dashboard showing pending requests.
     * * NOTE: This method is primarily used to return the specific lab queue view. 
     * The general dashboard statistics are handled by DashboardController.
     */
    public function dashboard()
{
    // Fetch all visits sent to the lab/radiology
    $pendingRequests = Visit::with(['patient', 'consultation.doctor'])
        ->where('status', 'Lab/Rad')
        ->orderBy('created_at', 'asc')
        ->get();

    return view('outpatient.lab.dashboard', compact('pendingRequests'));
}

    /**
     * Form to view and enter results for a specific LabRequest.
     */
    public function processRequest(LabRequest $labRequest)
    {
        // Placeholder for the form to enter results
        return view('outpatient.lab.process', compact('labRequest'));
    }

    /**
     * Stores the results and updates the status to 'Completed'.
     * Updates the visit status and redirects back to the lab dashboard.
     */
    public function storeResults(Request $request, LabRequest $labRequest)
    {
        $request->validate([
            'results_data' => 'required|string', // Or more complex validation
        ]);
        
        $labRequest->update([
            'results' => $request->input('results_data'),
            'status' => 'Completed',
            'lab_tech_id' => auth()->id(),
        ]);
        
        // Critically, we now update the Visit status to indicate results are ready for doctor review
        $labRequest->visit->update(['status' => 'Lab/Rad Results Ready']);

        // --- UPDATED REDIRECTION ---
        // Redirect the lab technician back to the main outpatient dashboard queue view for their role.
        return redirect()->route('outpatient.dashboard', ['role' => 'labtech']) 
                         ->with('success', 'Lab results recorded successfully. Patient moved to review queue.');
    }
}
