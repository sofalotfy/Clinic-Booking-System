<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Dashboard\GetOverview;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', GetOverview::execute());
    }
}
