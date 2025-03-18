<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFaqController extends Controller
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

        $faqs = $query->paginate(5)->withQueryString();

        return Inertia::render('Admin/AdminFAQ', [
            'faqs' => $faqs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outcome_area' => 'nullable|string',
            'program' => 'nullable|string',
            'questions' => 'nullable|string',
            'answers' => 'nullable|string',
        ]);

        Faq::create($validated);

        return redirect()->route('AdminFaqs')->with('success', 'FAQ Added Successfully!');
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'outcome_area' => 'nullable|string',
            'program' => 'nullable|string',
            'questions' => 'nullable|string',
            'answers' => 'nullable|string',
        ]);

        $faq->update($validated);

        return redirect()->route('AdminFaqs')->with('success', 'FAQ Updated Successfully!');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('AdminFaqs')->with('success', 'FAQ Deleted Successfully!');
    }
}