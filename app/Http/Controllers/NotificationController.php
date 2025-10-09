<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
{
    $notification = Auth::user()->notifications()->findOrFail($id);
    $notification->markAsRead();

    return response()->json(['success' => true]);
}

}
