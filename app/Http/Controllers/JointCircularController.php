<?php

namespace App\Http\Controllers;

use App\Models\JointCircular;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JointCircularController extends Controller
{
    public function index(Request $request)
    {
        $query = JointCircular::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        $circulars = $query->paginate(10);

        $dates = JointCircular::distinct('date')
            ->pluck('date');

        $response = [
            'dates' => $dates,
            'pagination' => $circulars,
            'filters' => $request->only(['search', 'date']),
        ];

        return Inertia::render('Guest/JointCirculars', $response);
    }
}
