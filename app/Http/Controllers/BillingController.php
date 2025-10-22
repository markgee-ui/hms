<?php

namespace App\Http\Controllers;
use App\Models\Visit;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    //
    // In App\Http\Controllers\BillingController.php
// Assuming you have a Visit model

public function billingQueue(Request $request)
{
    $search = $request->input('search');

    $query = Visit::with('patient')
        ->where('status', 'Billing') // Key filter from Pharmacy
        ->orderBy('updated_at', 'asc');

    // Search logic (optional)
    if (!empty($search)) {
        $query->whereHas('patient', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })->orWhere('visit_token', 'like', "%{$search}%");
    }

    $visits = $query->paginate(10);

    return view('outpatient.billing.queue', compact('visits'));
}
}
