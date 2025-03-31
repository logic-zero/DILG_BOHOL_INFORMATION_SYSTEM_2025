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

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('loFilter') && $request->loFilter === 'lo') {
            $query->where('title', 'like', 'LO-%');
        }

        $opinions = $query->paginate(10);

        $categories = LegalOpinion::distinct('category')->pluck('category');

        $response = [
            'categories' => $categories,
            'pagination' => $opinions,
            'filters' => $request->only(['search', 'category', 'loFilter']),
        ];

        return Inertia::render('Guest/LegalOpinions', $response);
    }
}