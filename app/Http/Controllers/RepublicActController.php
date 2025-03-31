<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RepublicAct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RepublicActController extends Controller
{
    public function index(Request $request)
    {
        $query = RepublicAct::query();

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        $acts = $query->paginate(10);

        $dates = RepublicAct::distinct('date')
            ->pluck('date');

        $response = [
            'dates' => $dates,
            'pagination' => $acts,
            'filters' => $request->only(['search', 'date']),
        ];

        return Inertia::render('Guest/RepublicActs', $response);
    }
}