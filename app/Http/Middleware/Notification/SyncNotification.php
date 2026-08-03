<?php

namespace App\Http\Middleware\Notification;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Notification;

class SyncNotification
{
    public function handle(Request $request, Closure $next): Response
    {
        $notifications = Notification::where('user_id', auth()->id())->where('viewed',false)->get();

        session(['notifications' => $notifications , 'notifications_count'  => count($notifications)]);

        return $next($request);
    }   
}
