<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\LabRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main Outpatient Dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role;
        $data = [];

        /**
         * -------------------------------------------------------------
         * FLOW COUNTS: All patients today grouped by status
         * -------------------------------------------------------------
         */
          // Step 1: Fetch all specific status counts for today
        $rawCounts = Visit::select('status', DB::raw('count(*) as count'))
            // ->whereDate('registration_date', now()->toDateString())
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Step 2: Aggregate the detailed statuses into the main flow stages
        $data['flowCounts'] = [
            // Registrtiion queue: Patients currently in registration
            'Registered' => $rawCounts['Registered'] ?? 0,
            
            // Triage Queue: Patients waiting for or currently in Triage
            'Triage' => ($rawCounts['Waiting for Triage'] ?? 0) + ($rawCounts['In Triage'] ?? 0),

            // Consultation Queue: Patients waiting for the Doctor ('Triage Completed') or actively being seen ('In Consultation')
            'Consultation' => ($rawCounts['Triage Completed'] ?? 0) + ($rawCounts['In Consultation'] ?? 0),
    
            // Subsequent stages
            'Lab/Rad' => $rawCounts['Lab/Rad'] ?? 0,
            'Pharmacy' => $rawCounts['Pharmacy'] ?? 0,
            'Billing' => $rawCounts['Billing'] ?? 0,
        ];

        /**
         * -------------------------------------------------------------
         * ROLE-SPECIFIC QUEUES
         * -------------------------------------------------------------
         */
        switch ($role) {
            case 'receptionist':
                $query = Visit::with('patient')
                    ->whereIn('status', ['Registered', 'Completed'])
                    ->whereDate('registration_date', now()->toDateString());

                // Apply filters dynamically
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }

                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('patient', function ($p) use ($search) {
                            $p->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('visit_token', 'like', "%{$search}%");
                    });
                }

                // Paginate after filters
                $data['receptionQueue'] = $query->orderBy('registration_date', 'asc')->paginate(10);
                break;

            case 'nurse':
               // Nurse logic: Triage Queue with Filtering, Searching, and Pagination
                $query = Visit::with('patient');

                // --- BASE LOGIC: Show Awaiting and Completed (all dates) ---
                if (!$request->filled('status')) {
                    // Default view: Show all waiting patients AND all completed patients (any day)
                    $query->whereIn('status', ['Waiting for Triage', 'Triage Completed']);
                } else {
                    // If a specific status is filtered:
                    // Show all patients matching the selected status (any date).
                    $query->where('status', $request->status);
                }
                // --- END BASE LOGIC ---
                  // --- NEW LOGIC: Apply Triage Category/Priority Filter (using whereHas) ---
              if ($request->filled('triage_priority')) {
                 $priority = $request->triage_priority;
        
                   // Use whereHas to filter Visits based on a condition in the related Triage model
                  $query->whereHas('triage', function ($t) use ($priority) {
               // Using the confirmed column name: 'triage_category'
                    $t->where('triage_category', $priority);
                  });
                }
                // Apply search filter
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('patient', function ($p) use ($search) {
                            $p->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('visit_token', 'like', "%{$search}%");
                    });
                }

                // Apply dynamic sorting and pagination
                $data['triageQueue'] = $query
                    // 1. Primary Sort: Prioritize waiting patients first, then completed patients.
                    ->orderByRaw("FIELD(status, 'Waiting for Triage', 'Triage Completed')")
                    
                    // 2. Secondary Sort for 'Waiting for Triage': Oldest queue time first (ASC).
                    // We use NULLIF to only sort by triage_queue_time when the status matches.
                    ->orderByRaw("NULLIF(status, 'Waiting for Triage') IS NULL DESC") // Ensure this status is prioritized in the next order by
                    ->orderBy('triage_queue_time', 'asc') 

                    // 3. Secondary Sort for 'Triage Completed': Latest update time first (DESC).
                    // Using 'updated_at' as a reliable timestamp for when the record was last modified/completed.
                    ->orderByRaw("CASE WHEN status = 'Triage Completed' THEN updated_at END DESC") 

                    ->paginate(10); 
                break;
              //doctor function start 10/13/25  
            case 'doctor':
                 // WORKFLOW STEP 3: DOCTOR'S CONSULTATION QUEUE
                $query = Visit::with(['patient', 'triage', 'consultation']);
                // Show patients who have completed triage but not yet completed consultation
                if (!$request->filled('status')) {
                    $query->whereIn('status', ['Triage Completed','consultation']); // Removed whereDate constraint
                } else {
                    // Filter by a specific status (all dates)
                    $query->where('status', $request->status); // Removed whereDate constraint
                }
                // --- NEW LOGIC: Apply Triage Category/Priority Filter (using whereHas) ---
                if ($request->filled('triage_priority')) {
                 $priority = $request->triage_priority;
        
                   // Use whereHas to filter Visits based on a condition in the related Triage model
                  $query->whereHas('triage', function ($t) use ($priority) {
               // Using the confirmed column name: 'triage_category'
                    $t->where('triage_category', $priority);
                  });
                }
                // Apply search filter (by Patient Name or Visit Token)
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('patient', function ($p) use ($search) {
                            $p->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('visit_token', 'like', "%{$search}%");
                    });
                }
                
                // Apply dynamic sorting and pagination
                $data['consultationQueue'] = $query
                    // Primary Sort: Prioritize 'Triage Completed' (waiting) first, then 'In Consultation'.
                    ->orderByRaw("FIELD(status, 'Triage Completed', 'In Consultation', 'Consultation Completed')")
                    // Secondary Sort: Oldest patient waiting in the 'Triage Completed' status first (Time is proxy for queue time).
                    ->orderBy('updated_at', 'asc') 
                    ->paginate(10);
                break;

              case 'labtech':
                                    // WORKFLOW STEP 4: LAB TECHNICIAN'S QUEUE
                                     // We now query the Visit model and eager load the LabRequests (which contain the doctor info).
                    $query = Visit::with(['patient', 'labRequests.doctor']);

                            // CRITICAL FILTER: Only include Visits that are currently 
                               // in the 'Lab/Rad' status. This is the only mandatory filter.
                     $query->where('status', 'Lab/Rad'); // Direct filter on the Visit model

                        // NOTE: The LabQueue will now contain Visit objects.
                        // The Blade view (outpatient.lab.dashboard) must be updated to access
                        // LabRequests via $visit->labRequests.

                        // Apply search filter (by Patient Name or Visit Token)
                     if ($request->filled('search')) {
                  $search = $request->search;
                    $query->where(function ($q) use ($search) {
                           // Search by patient name (direct relationship on Visit)
                       $q->whereHas('patient', function ($p) use ($search) {
                        $p->where('name', 'like', "%{$search}%");
                      })
                            // Or by visit token (direct column on Visit model)
                       ->orWhere('visit_token', 'like', "%{$search}%");
                      });
                  }

                           // Sorting: Show the oldest patients/visits first (using registration_date from Visit)
                  $data['labQueue'] = $query
                    ->orderBy('registration_date', 'asc')
                    ->paginate(10);
                 break;

             case 'pharmacist':
    // WORKFLOW STEP 5: PHARMACIST'S QUEUE
    // Fetch prescriptions that have been sent from doctors and are pending dispensation.

    $query = Prescription::with(['visit.patient', 'doctor']);

    // Only prescriptions currently awaiting pharmacy action
    $query->where('status', 'Pending');

    // Optional search by patient name or visit token
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->whereHas('patient', function ($p) use ($search) {
                $p->where('name', 'like', "%{$search}%");
            })
            ->orWhereHas('visit', function ($v) use ($search) {
                $v->where('visit_token', 'like', "%{$search}%");
            });
        });
    }

    // Optional filter by status (Pending Pharmacy or Dispensed)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Sorting and pagination
    $data['pharmacyQueue'] = $query
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    break;

            case 'cashier':
                // WORKFLOW STEP 6: BILLING QUEUE
                $query = Visit::with('patient')
                    ->where('status', 'Billing'); // Only visits in Billing status

                // Apply search filter (by Patient Name or Visit Token)
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('patient', function ($p) use ($search) {
                            $p->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('visit_token', 'like', "%{$search}%");
                    });
                }

                // Paginate after filters
                $data['billingQueue'] = $query->orderBy('updated_at', 'asc')->paginate(10);
                break;



            default:
                // Optional: handle other roles gracefully
                $data['receptionQueue'] = collect();
                break;
        }

        /**
         * -------------------------------------------------------------
         * Return Dashboard View
         * -------------------------------------------------------------
         */
        return view('outpatient.dashboard', compact('data', 'role'));
    }

    /**
     * -------------------------------------------------------------
     * Fetch Notifications for Current User (AJAX)
     * -------------------------------------------------------------
     */
   public function fetchNotifications()
{
    $user = Auth::user();

    $notifications = $user->notifications()->latest()->take(10)->get();

    $unreadCount = $user->unreadNotifications()->count();

    // Transform data to make it consistent with your UI
    $formatted = $notifications->map(function ($notification) {
        $data = $notification->data;
        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notification',
            'message' => $data['message'] ?? '',
            'icon' => $data['icon'] ?? 'info',
            'link' => $data['link'] ?? '#',
            'is_read' => $notification->read_at ? true : false,
            'created_at' => $notification->created_at->diffForHumans(),
        ];
    });

    return response()->json([
        'notifications' => $formatted,
        'unreadCount' => $unreadCount,
    ]);
}

}
