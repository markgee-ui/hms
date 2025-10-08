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
                $data['triageQueue'] = Visit::with('patient')
                    ->where('status', 'Waiting for Triage')
                    ->whereDate('registration_date', now()->toDateString())
                    ->orderBy('registration_date', 'asc')
                    ->get();
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

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
