<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Notification;
use Illuminate\Http\Request;
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
        $data['flowCounts'] = Visit::select('status', DB::raw('count(*) as count'))
            ->whereDate('registration_date', now()->toDateString())
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses exist even if 0
        $allStatuses = ['Registered', 'Triage', 'Consultation', 'Lab/Rad', 'Pharmacy', 'Billing'];
        foreach ($allStatuses as $status) {
            $data['flowCounts'][$status] = $data['flowCounts'][$status] ?? 0;
        }

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
                
            case 'doctor':
                $data['consultationQueue'] = Visit::with(['patient', 'triage'])
                    ->where('status', 'Triage Completed ')
                    ->whereDate('registration_date', now()->toDateString())
                    ->orderBy('updated_at', 'asc')
                    ->get();
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
