<?php

namespace App\Http\Controllers;

use App\Models\Knowledge_Materials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminKnowledge_MaterialsController extends Controller
{
    public function index(Request $request)
    {
        $query = Knowledge_Materials::orderBy('date', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                  ->orWhere('link', 'LIKE', "%$search%");
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
            'link' => 'nullable|url',
            'date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:102400',
        ]);

        $uploadPath = public_path('knowledge_materials');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('file')) {
            $fileName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        }

        Knowledge_Materials::create($validated);

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material added successfully!');
    }

    public function update(Request $request, Knowledge_Materials $knowledgeMaterial)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'link' => 'nullable|url',
            'date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:102400',
        ]);

        $uploadPath = public_path('knowledge_materials');

        if ($request->hasFile('file')) {
            if ($knowledgeMaterial->file) {
                $filePath = public_path('knowledge_materials/' . $knowledgeMaterial->file);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $fileName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        } else {
            unset($validated['file']);
        }

        $knowledgeMaterial->update($validated);

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material updated successfully!');
    }

    public function destroy(Knowledge_Materials $knowledgeMaterial)
    {
        if ($knowledgeMaterial->file) {
            $filePath = public_path('knowledge_materials/' . $knowledgeMaterial->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $knowledgeMaterial->delete();

        return redirect()->route('AdminKnowledgeMaterials')->with('success', 'Material deleted successfully!');
    }

    public function download(Knowledge_Materials $knowledgeMaterial)
    {
        if (!$knowledgeMaterial->file) {
            abort(404);
        }

        $filePath = public_path('knowledge_materials/' . $knowledgeMaterial->file);
        if (!File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }
}
