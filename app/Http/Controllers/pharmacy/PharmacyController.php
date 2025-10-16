<?php

namespace App\Http\Controllers\pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Drug;
use App\Models\Dispense;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    // The pharmacist’s main view (queue) is handled here.
    // This controller manages prescriptions sent by doctors, 
    // verifies drug availability, and records dispensation actions.

    /**
     * Display all prescriptions awaiting pharmacy action.
     */
    public function pharmacyQueue(Request $request)
    {
        $pharmacistId = auth()->id();

        // Optional filters
        $search = $request->input('search');
        $statusFilter = $request->input('status');

        // Base query
        $query = Prescription::with(['visit.patient', 'doctor'])
            ->orderBy('created_at', 'desc');

        // Logic fix: apply status filter (or default to 'Pending')
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        } else {
            // Default to 'Pending' status if no filter is applied
            $query->where('status', 'Pending');
        }

        // Optional search (by patient name or visit token)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('visit.patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('visit', function ($q) use ($search) {
                    $q->where('visit_token', 'like', "%{$search}%");
                });
            });
        }

        $prescriptions = $query->paginate(10);

        return view('outpatient.pharmacy.queue', [
            'prescriptions' => $prescriptions,
            'filters' => [
                'search' => $search,
                'status' => $statusFilter,
            ],
        ]);
    }

    /**
     * NEW: Display detailed patient information, prescription, and drug availability check.
     */
    public function viewPrescription($id)
    {
        // Load the prescription with necessary relationships: Patient, Visit, Consultation
        $prescription = Prescription::with([
            'visit.patient', 
            'visit.consultation', 
            'doctor'
        ])->findOrFail($id);
        
        // --- SIMULATED DRUG AVAILABILITY CHECK ---
        // In a real system, you would parse $prescription->prescription_details 
        // and check against the Drug/Inventory model (Drug::whereIn(...)->get()).
        
        // Simulation: Assume a random availability status for demonstration
        $isAvailable = (bool)rand(0, 1);
        
        $drugAvailability = [
            'isAvailable' => $isAvailable,
            'stockLevel' => $isAvailable ? rand(10, 100) : 0,
            'simulatedCheckNote' => $isAvailable 
                ? 'Check successful. Stock level is sufficient.' 
                : 'Warning: Stock is currently zero. Requires ordering or substitution.'
        ];

        // Assuming a view named 'outpatient.pharmacy.view' will display this data
        return view('outpatient.pharmacy.view', compact('prescription', 'drugAvailability'));
    }

    /**
     * Quick action to process a specific prescription (simple status change).
     */
    public function process($id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->status = 'Dispensed';
        $prescription->save();

        return redirect()->back()->with('success', 'Prescription dispensed successfully.');
    }


    /**
     * Stores dispensation details and updates the prescription + visit status.
     */
    public function storeDispense(Request $request, Prescription $prescription)
    {
        $request->validate([
            'dispense_notes' => 'nullable|string',
        ]);

        // Update prescription status and attach pharmacist info
        $prescription->update([
            'status' => 'Dispensed',
            'pharmacist_id' => auth()->id(),
            'dispense_notes' => $request->input('dispense_notes'),
        ]);

        // Optionally update the visit to "Medication Dispensed"
        $prescription->visit->update(['status' => 'Billing']);

        return redirect()->route('outpatient.dashboard', ['role' => 'pharmacist'])
                            ->with('success', 'Medication dispensed successfully. Patient sent to Billing queue.');
    }

    /**
     * View all previously dispensed prescriptions.
     */
    public function dispensedHistory(Request $request)
    {
        $pharmacistId = auth()->id();

        $query = Prescription::with(['visit.patient', 'doctor'])
            ->where('pharmacist_id', $pharmacistId)
            ->where('status', 'Dispensed')
            ->orderBy('updated_at', 'desc');

        $search = $request->input('search');
        if (!empty($search)) {
            $query->whereHas('visit.patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $dispensed = $query->paginate(10);

        return view('outpatient.pharmacy.history', [
            'dispensed' => $dispensed,
            'filters' => ['search' => $search],
        ]);
    }

}