<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = Activity::where('log_name', 'default')
                        ->select('description', 'created_at', 'causer_id')
                        ->with('causer')
                        ->latest()
                        ->paginate(20);

        return Inertia::render('Admin/AdminLogs', [
            'logs' => $logs
        ]);
    }
}
