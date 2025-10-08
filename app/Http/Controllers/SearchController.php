<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handles the global search for patients and visits via AJAX.
     * Searches for patients by name and visits by token or associated patient name.
     */
    public function index(Request $request)
    {
        $query = $request->query('q');

        // Immediately return empty arrays if the query is too short or empty
        if (empty($query) || strlen($query) < 2) {
            return response()->json(['patients' => [], 'visits' => []]);
        }
        
        // --- 1. Search Patients by Name ---
        $patients = Patient::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            // CRITICAL FIX: Changed 'dob' and 'gender' to 'age' and added 'phone'
            // to match the likely columns present in the Patient table.
            ->get(['id', 'name', 'age', 'phone']);

        // --- 2. Search Visits by Token OR Patient Name (Defensive Query) ---
        $visits = Visit::with('patient:id,name') // Eagerly load patient name
            ->where(function ($q) use ($query) {
                // Search by visit token
                $q->where('visit_token', 'LIKE', "%{$query}%");
            })
            // SECOND CLAUSE: Search by Patient Name
            ->orWhere(function ($q) use ($query) {
                // Ensure patient_id exists before trying to run orWhereHas
                $q->whereNotNull('patient_id')
                  ->whereHas('patient', function ($subQ) use ($query) {
                      $subQ->where('name', 'LIKE', "%{$query}%");
                  });
            })
            ->limit(5)
            // Selecting essential visit columns
            ->get(['id', 'patient_id', 'visit_token', 'status', 'registration_date']);

        return response()->json([
            'patients' => $patients,
            'visits' => $visits,
        ]);
    }
}
