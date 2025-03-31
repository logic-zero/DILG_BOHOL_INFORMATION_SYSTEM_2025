<?php

namespace App\Http\Controllers;

use App\Models\Knowledge_Materials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GuestKnowledge_MaterialsController extends Controller
{
    public function index(Request $request)
    {
        $query = Knowledge_Materials::orderBy('date', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        $materials = $query->paginate(10)->withQueryString();

        return Inertia::render('Guest/KnowledgeMaterials', [
            'materials' => $materials,
            'filters' => $request->only(['search'])
        ]);
    }

    public function download(Knowledge_Materials $knowledgeMaterial)
    {
        if (!$knowledgeMaterial->file) {
            abort(404);
        }

        return Storage::disk('public')->download($knowledgeMaterial->file);
    }
}
