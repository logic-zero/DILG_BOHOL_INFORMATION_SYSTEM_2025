<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PresidentialDirective;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PresidentialDirectiveController extends Controller
{
    public function index(Request $request)
    {
        $query = PresidentialDirective::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        // Date filter - only apply if a specific date is selected
        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        // Paginate and order results - newest first
        $directives = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc') // secondary sort
            ->paginate(10);

        // Get all distinct dates (not just those on current page)
        $dates = PresidentialDirective::select('date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        // Prepare the response
        $response = [
            'dates' => $dates,
            'pagination' => $directives,
            'filters' => $request->only(['search', 'date']),
        ];

        return Inertia::render('Guest/PresidentialDirectives', $response);
    }
}