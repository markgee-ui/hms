<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Fetch the user's latest notifications and unread count, formatted for the JS front-end.
     */
    public function fetch()
    {
        $user = Auth::user();

        // Get the latest 10 unread notifications
        $unreadNotifications = $user->unreadNotifications()->latest()->limit(10)->get();

        // Get the latest read notifications to fill the list, ensuring we don't duplicate
        $readNotifications = $user->readNotifications()
                                  ->whereNotIn('id', $unreadNotifications->pluck('id'))
                                  ->latest('read_at')
                                  ->limit(10 - $unreadNotifications->count())
                                  ->get();

        // Combine the lists (unread first) and map to the format expected by the frontend JavaScript
        $notifications = $unreadNotifications->merge($readNotifications)->map(function ($notification) {
            
            // The data structure comes from the toDatabase method of the NewTriagePatientNotification
            $data = $notification->data;
            
            return [
                'id' => $notification->id,
                'title' => $data['title'] ?? 'System Notification',
                'message' => $data['message'] ?? 'Check your dashboard.', 
                'icon' => $data['icon'] ?? 'bell', 
                'link' => $data['link'] ?? '#', 
                'created_at' => $notification->created_at,
                'is_read' => $notification->read_at !== null,
            ];
        });

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a single notification as read by ID.
     */
    public function markAsRead($id)
    {
        // Ensures the notification belongs to the authenticated user before marking it
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
    
    /**
     * Mark all unread notifications for the current user as read (used by 'Clear All').
     */
    public function markAllRead()
    {
        $user = Auth::user();
        
        // Mark all unread notifications as read
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }
}
