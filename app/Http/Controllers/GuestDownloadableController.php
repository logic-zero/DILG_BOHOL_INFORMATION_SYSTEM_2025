<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Downloadable;
use Illuminate\Http\Request;

class GuestDownloadableController extends Controller
{
    public function index(Request $request)
    {
        $query = Downloadable::orderBy('created_at', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('link', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('outcome_area')) {
            $outcome_area = $request->outcome_area;
            $query->where('outcome_area', 'LIKE', "%$outcome_area%");
        }

        $downloadables = $query->paginate(5)->withQueryString();

        $outcome_areas = Downloadable::distinct()->pluck('outcome_area')->filter()->values();

        return Inertia::render('Guest/Downloadables', [
            'downloadables' => $downloadables,
            'filters' => $request->only(['search', 'outcome_area']),
            'outcome_areas' => $outcome_areas,
        ]);
    }
}
