<?php

namespace App\Http\Controllers;

use App\Models\Knowledge_Materials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminKnowledge_MaterialsController extends Controller
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

        return Inertia::render('Admin/AdminKnowledgeMaterials', [
            'materials' => $materials,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('knowledge_materials', 'public');
        }

        Knowledge_Materials::create($validated);

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material added successfully!');
    }

    public function update(Request $request, Knowledge_Materials $knowledgeMaterial)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        if ($request->hasFile('file')) {
            if ($knowledgeMaterial->file) {
                Storage::disk('public')->delete($knowledgeMaterial->file);
            }
            $validated['file'] = $request->file('file')->store('knowledge_materials', 'public');
        } else {
            unset($validated['file']);
        }

        $knowledgeMaterial->update($validated);

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material updated successfully!');
    }

    public function destroy(Knowledge_Materials $knowledgeMaterial)
    {
        if ($knowledgeMaterial->file) {
            Storage::disk('public')->delete($knowledgeMaterial->file);
        }

        $knowledgeMaterial->delete();

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material deleted successfully!');
    }

    public function download(Knowledge_Materials $knowledgeMaterial)
    {
        if (!$knowledgeMaterial->file) {
            abort(404);
        }

        return Storage::disk('public')->download($knowledgeMaterial->file);
    }
}
