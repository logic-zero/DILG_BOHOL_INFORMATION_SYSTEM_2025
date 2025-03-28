<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GuestJobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::orderBy('created_at', 'DESC');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position', 'LIKE', "%$search%")
                    ->orWhere('details', 'LIKE', "%$search%")
                    ->orWhere('link', 'LIKE', "%$search%")
                    ->orWhere('remarks', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('position')) {
            $position = $request->position;
            $query->where('position', 'LIKE', "%$position%");
        }

        if ($request->filled('remarks')) {
            $remarks = $request->remarks;
            $query->where('remarks', 'LIKE', "%$remarks%");
        }

        $jobs = $query->paginate(5)->withQueryString();

        // Fetch unique values for positions and remarks
        $positions = Job::distinct()->pluck('position')->filter()->values();
        $remarks = Job::distinct()->pluck('remarks')->filter()->values();

        return Inertia::render('Guest/JobVacancies', [
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'position', 'remarks']),
            'positions' => $positions, // Pass unique positions
            'remarks' => $remarks, // Pass unique remarks
        ]);
    }
}

// class GuestJobController extends Controller
// {
//     public function index(Request $request)
//     {
//         $query = Job::orderBy('created_at', 'DESC');

//         // Search filter
//         if ($request->filled('search')) {
//             $search = $request->search;
//             $query->where(function ($q) use ($search) {
//                 $q->where('position', 'LIKE', "%$search%")
//                     ->orWhere('details', 'LIKE', "%$search%")
//                     ->orWhere('link', 'LIKE', "%$search%")
//                     ->orWhere('remarks', 'LIKE', "%$search%");
//             });
//         }

//         if ($request->filled('position')) {
//             $position = $request->position;
//             $query->where('position', 'LIKE', "%$position%");
//         }

//         if ($request->filled('remarks')) {
//             $remarks = $request->remarks;
//             $query->where('remarks', 'LIKE', "%$remarks%");
//         }

//         $jobs = $query->paginate(10)->withQueryString();

//         // Fetch unique values for positions and remarks
//         $positions = Job::distinct()->pluck('position')->filter()->values();
//         $remarks = Job::distinct()->pluck('remarks')->filter()->values();

//         return Inertia::render('Guest/JobVacancies', [
//             'jobs' => $jobs,
//             'filters' => $request->only(['search', 'position', 'remarks']),
//             'positions' => $positions, // Pass unique positions
//             'remarks' => $remarks, // Pass unique remarks
//         ]);
//     }
// }