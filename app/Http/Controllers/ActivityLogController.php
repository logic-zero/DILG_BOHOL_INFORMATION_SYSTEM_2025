<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Activity::where('log_name', 'default')
                        ->select('description', 'created_at', 'causer_id')
                        ->with('causer')
                        ->when($request->search, function ($query, $search) {
                            $query->where('description', 'like', "%{$search}%");
                        })
                        ->when($request->from_date, function ($query, $fromDate) {
                            $query->whereDate('created_at', '>=', Carbon::parse($fromDate));
                        })
                        ->when($request->to_date, function ($query, $toDate) {
                            $query->whereDate('created_at', '<=', Carbon::parse($toDate));
                        })
                        ->latest()
                        ->paginate(20);

        return Inertia::render('Admin/AdminLogs', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'from_date', 'to_date'])
        ]);
    }
}
