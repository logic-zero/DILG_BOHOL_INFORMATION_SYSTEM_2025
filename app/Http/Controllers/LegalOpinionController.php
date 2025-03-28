<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LegalOpinion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LegalOpinionController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalOpinion::query();

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // No Title Opinions filter (titles starting with "LO")
        if ($request->has('loFilter') && $request->loFilter === 'lo') {
            $query->where('title', 'like', 'LO-%');
        }

        // Paginate the results
        $opinions = $query->paginate(10);

        // Get distinct categories
        $categories = LegalOpinion::distinct('category')->pluck('category');

        // Prepare the response
        $response = [
            'categories' => $categories,
            'pagination' => $opinions,
            'filters' => $request->only(['search', 'category', 'loFilter']),
        ];

        // Log the response for debugging
        // \Log::info('Inertia Response:', $response);

        return Inertia::render('Guest/LegalOpinions', $response);
    }
}