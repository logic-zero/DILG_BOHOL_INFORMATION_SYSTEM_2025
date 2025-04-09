<?php

namespace App\Http\Controllers;

use App\Models\Downloadable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminDownloadableController extends Controller
{
    public function index(Request $request)
    {
        $query = Downloadable::orderBy('created_at', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%")
                    ->orWhere('link', 'LIKE', "%$search%")
                    ->orWhere('outcome_area', 'LIKE', "%$search%")
                    ->orWhere('program', 'LIKE', "%$search%");
            });
        }

        $downloadables = $query->paginate(10)->withQueryString();

        $outcomeAreas = Downloadable::select('outcome_area')
            ->distinct()
            ->whereNotNull('outcome_area')
            ->pluck('outcome_area');

        $programs = Downloadable::select('program')
            ->distinct()
            ->whereNotNull('program')
            ->pluck('program');

        return Inertia::render('Admin/AdminDownloadables', [
            'downloadables' => $downloadables,
            'filters' => $request->only(['search']),
            'outcomeAreas' => $outcomeAreas,
            'programs' => $programs,
        ]);
    }


    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);

        $validated = $request->validate([
            'title' => 'required|string',
            'link' => 'nullable|string',
            'outcome_area' => 'nullable|string',
            'program' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
        ]);

        $uploadPath = public_path('downloadable_files');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('file')) {
            $fileName = $request->file('file')->getClientOriginalName();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        }

        Downloadable::create($validated);

        return redirect()->route('AdminDownloadables')->with('success', 'Downloadable Added Successfully!');
    }

    public function update(Request $request, Downloadable $downloadable)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'link' => 'nullable|string',
            'outcome_area' => 'nullable|string',
            'program' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt|max:10240',
        ]);

        $uploadPath = public_path('downloadable_files');

        if ($request->hasFile('file')) {
            if ($downloadable->file) {
                $filePath = $uploadPath . '/' . $downloadable->file;
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }

            $fileName = $request->file('file')->getClientOriginalName();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        }

        $downloadable->update($validated);

        return redirect()->route('AdminDownloadables')->with('success', 'Downloadable Updated Successfully!');
    }

    public function destroy(Downloadable $downloadable)
    {
        if ($downloadable->file) {
            $filePath = public_path('downloadable_files/' . $downloadable->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $downloadable->delete();

        return redirect()->route('AdminDownloadables')->with('success', 'Downloadable Deleted Successfully!');
    }
}
