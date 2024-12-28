<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;


class NotificationControllerApi extends Controller
{

    // Method to get all notifications for the logged-in user
    public function getUserNotifications()
    {
        $userId = Auth::id(); // Get the ID of the logged-in user

        // Fetch notifications for the logged-in user
        $notifications = DB::table('notifications')->where('to_user', $userId)
            ->select('id', 'type', 'from_user', 'to_user', 'is_read','title', 'message', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'message' => 'Data History',
                'status' => 'success',
                'data' => $notifications
            ]);
    }

    // Method to mark a specific notification as read
    public function markAsRead(Request $request, $id)
    {
        $notification = DB::table('notifications')->where('id', $id)
            ->where('to_user', Auth::id()) // Ensure only the user's notifications can be marked as read
            ->first();

        if ($notification) {
            DB::table('notifications')->where('id', $id)
            ->where('to_user', Auth::id())->update([
                'is_read' => 1
            ]);

            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json(['message' => 'Notification not found.'], 404);
    }

    public function sendNotification($user, $message)
    {
        // Implementasikan logika untuk mengirim notifikasi, misalnya dengan menggunakan email, push notification, dll.
        return response()->json([
            'status' => 'success',
            'message' => 'Notification sent successfully!'
        ], 200);
    }
}
