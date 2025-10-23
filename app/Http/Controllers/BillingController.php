<?php

namespace App\Http\Controllers;
use App\Models\Visit;

use Illuminate\Http\Request;

class BillingController extends Controller
{


public function billingQueue(Request $request)
{
    $search = $request->input('search');

    $query = Visit::with('patient')
        ->where('status', 'Billing') 
        ->orderBy('updated_at', 'asc');

    // Search logic
    if (!empty($search)) {
        $query->whereHas('patient', function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%");
        })->orWhere('visit_token', 'like', "%{$search}%");
    }

    $visits = $query->paginate(10);

    return view('outpatient.billing.queue', compact('visits'));
}
}
