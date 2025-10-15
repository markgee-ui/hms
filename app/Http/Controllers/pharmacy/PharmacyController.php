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

        // Base query: only prescriptions pending at the pharmacy
        $query = Prescription::with(['visit.patient', 'doctor'])
            ->where('status', 'Pending Pharmacy')
            ->orderBy('created_at', 'desc');

        // Optional search (by patient name or visit token)
        if (!empty($search)) {
            $query->whereHas('visit.patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('visit', function ($q) use ($search) {
                $q->where('visit_token', 'like', "%{$search}%");
            });
        }

        // Optional filter by status (Pending Pharmacy / Dispensed)
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
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
     * Form to process (dispense) a specific prescription.
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
        $prescription->visit->update(['status' => 'Medication Dispensed']);

        return redirect()->route('outpatient.dashboard', ['role' => 'pharmacist'])
                         ->with('success', 'Medication dispensed successfully. Patient cleared from pharmacy queue.');
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
