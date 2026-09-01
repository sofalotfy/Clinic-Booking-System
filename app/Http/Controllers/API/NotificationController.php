<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Notifications\ListNewNotifications;
use App\APIServices\Notifications\ListHistoricalNotifications;
use App\APIServices\Notifications\ViewNotification;
use App\APIServices\Notifications\ViewBulkNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'notifications' => ListNewNotifications::execute($request),
        ]);
    }

    public function historical(Request $request)
    {
        return response()->json([
            'success' => true,
            'notifications' => ListHistoricalNotifications::execute($request),
        ]);
    }

    public function view(Request $request,Notification $notification)
    {
        return response()->json([
            'success' => true,
            'notification' => ViewNotification::execute($request, $notification),
        ]);
    }   

    public function viewBulk(Request $request)
    {
        return response()->json([
            'success' => true,
            'notifications' => ViewBulkNotification::execute($request),
        ]);
    }   
}
