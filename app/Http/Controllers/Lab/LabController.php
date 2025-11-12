<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\LabRequest;
use Illuminate\Http\Request;
use App\Models\User; 
use App\Notifications\LabResultsReadyNotification;
use Illuminate\Support\Facades\Notification;

class LabController extends Controller
{
    // The previous 'dashboard' method has been removed. 
    // The lab technician's main view (the queue) is now handled by 
    // the DashboardController@index method when the user's role is 'labtech'.
    // 
    // **ACTION REQUIRED (Outside this file):**
    // Update the route that currently points to this controller's dashboard 
    // method (e.g., '/lab/dashboard') to redirect or route directly to:
    // route('outpatient.dashboard')
    // Laravel will automatically detect the user's 'labtech' role and 
    // load the correct queue view from the main DashboardController.

    /**
     * Form to view and enter results for a specific LabRequest.
     */
    public function processRequest(LabRequest $labRequest)
    {
        // Placeholder for the form to enter results
        // Ensure this view uses $labRequest, which is correctly passed here.
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
        

        //notification logic
        $orderingDoctor = $labRequest->doctor;
         if ($orderingDoctor) {
            // 2. Send the notification to the specific doctor
            Notification::send(
                $orderingDoctor, 
                new LabResultsReadyNotification($labRequest->visit)
            );
        }

        // This redirection is correct and points to the unified dashboard controller.
        return redirect()->route('outpatient.dashboard', ['role' => 'labtech']) 
                         ->with('success', 'Lab results recorded successfully. Patient moved to review queue.');
    }

    public function labQueue(Request $request)
{
    $labtechId = auth()->id(); // current logged-in lab tech

    // Filters (optional)
    $search = $request->input('search');
    $statusFilter = $request->input('status');

    // Base query: only requests handled by this labtech
    $query = LabRequest::with(['visit.patient', 'doctor'])
        ->where('lab_tech_id', $labtechId)
        ->where(function ($q) {
            $q->where('status', 'Completed')
              ->orWhereNotNull('results');
        })
        ->orderBy('updated_at', 'desc');

    // Optional search (by patient name or visit token)
    if (!empty($search)) {
        $query->whereHas('visit.patient', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })->orWhereHas('visit', function ($q) use ($search) {
            $q->where('visit_token', 'like', "%{$search}%");
        });
    }

    // Optional filter by status (Completed / Pending Verification)
    if (!empty($statusFilter)) {
        $query->where('status', $statusFilter);
    }

    // Pagination
    $labResults = $query->paginate(10);

    return view('outpatient.lab.results', [
        'labResults' => $labResults,
        'filters' => [
            'search' => $search,
            'status' => $statusFilter,
        ],
    ]);
}

}
