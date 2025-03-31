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

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('date') && $request->date) {
            $query->where('date', $request->date);
        }

        $directives = $query->paginate(10);

        $dates = PresidentialDirective::distinct('date')
            ->pluck('date');

        $response = [
            'dates' => $dates,
            'pagination' => $directives,
            'filters' => $request->only(['search', 'date']),
        ];

        return Inertia::render('Guest/PresidentialDirectives', $response);
    }
}