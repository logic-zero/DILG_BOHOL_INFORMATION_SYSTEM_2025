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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('outcome_area', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%")
                    ->orWhere('questions', 'LIKE', "%$search%")
                    ->orWhere('answers', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('program')) {
            $query->where('program', $request->program);
        }

        if ($request->filled('outcome_area')) {
            $query->where('outcome_area', $request->outcome_area);
        }

        $faqs = $query->paginate(5)->withQueryString();

        $programs = Faq::whereNotNull('program')->distinct()->pluck('program')->values();
        $outcomeAreas = Faq::whereNotNull('outcome_area')->distinct()->pluck('outcome_area')->values();

        return Inertia::render('Guest/FAQs', [
            'faqs' => $faqs,
            'filters' => $request->only(['search', 'program', 'outcome_area']),
            'programs' => $programs,
            'outcomeAreas' => $outcomeAreas,
        ]);
    }
}
