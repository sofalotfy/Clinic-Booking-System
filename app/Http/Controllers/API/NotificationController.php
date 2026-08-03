<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\APIServices\Notifications\ListNewNotifications;
use App\APIServices\Notifications\ListHistoricalNotifications;
use App\APIServices\Notifications\ViewNotification;
use App\APIServices\Notifications\ViewBulkNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = ListNewNotifications::execute($request);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    public function historical(Request $request)
    {
        $notifications = ListHistoricalNotifications::execute($request);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    public function view(Request $request,Notification $notification)
    {
        $notification = ViewNotification::execute($request, $notification);
        
        return response()->json([
            'success' => true,
            'notification' => $notification,
        ]);
    }   

    public function viewBulk(Request $request)
    {
        $notifications = ViewBulkNotification::execute($request);
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }   
}
