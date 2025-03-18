<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GuestFaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::orderBy('created_at', 'DESC');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('outcome_area', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%")
                    ->orWhere('questions', 'LIKE', "%$search%")
                    ->orWhere('answers', 'LIKE', "%$search%");
            });
        }

        // Program filter
        if ($request->filled('program')) {
            $program = $request->program;
            $query->where('program', 'LIKE', "%$program%");
        }

        // Outcome Area filter
        if ($request->filled('outcome_area')) {
            $outcome_area = $request->outcome_area;
            $query->where('outcome_area', 'LIKE', "%$outcome_area%");
        }

        $faqs = $query->paginate(5)->withQueryString();

        // Fetch unique values for programs and outcome areas
        $programs = Faq::distinct()->pluck('program')->filter()->values();
        $outcomeAreas = Faq::distinct()->pluck('outcome_area')->filter()->values();

        return Inertia::render('Guest/FAQs', [
            'faqs' => $faqs,
            'filters' => $request->only(['search', 'program', 'outcome_area']),
            'programs' => $programs, // Pass unique programs
            'outcomeAreas' => $outcomeAreas, // Pass unique outcome areas
        ]);
    }
}