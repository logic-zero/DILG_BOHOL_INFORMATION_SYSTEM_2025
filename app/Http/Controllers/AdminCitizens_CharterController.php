<?php

namespace App\Http\Controllers;

use App\Models\Citizens_Charter;
use App\Models\Citizens_Charter_PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminCitizens_CharterController extends Controller
{
    public function index(Request $request)
    {
        $query = Citizens_Charter::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
            });
        }

        $charters = $query->paginate(5)->withQueryString();
        $pdf = Citizens_Charter_PDF::first();

        return Inertia::render('Admin/AdminCitizensCharter', [
            'charters' => $charters,
            'filters' => $request->only(['search']),
            'pdf' => $pdf,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'file' => 'required|file|mimes:mp4,mov,avi|max:102400',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('citizens_charters', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('citizens_charters/thumbnails', 'public');
        }

        Citizens_Charter::create($validated);

        return response()->json(['success' => 'Video added successfully.']);
    }

    public function update(Request $request, Citizens_Charter $citizens_charter)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'file' => 'nullable|file|mimes:mp4,mov,avi|max:102400',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('file')) {
            if ($citizens_charter->file) {
                Storage::disk('public')->delete($citizens_charter->file);
            }
            $validated['file'] = $request->file('file')->store('citizens_charters', 'public');
        } else {
            unset($validated['file']);
        }

        if ($request->hasFile('thumbnail')) {
            if ($citizens_charter->thumbnail) {
                Storage::disk('public')->delete($citizens_charter->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('citizens_charters/thumbnails', 'public');
        } else {
            unset($validated['thumbnail']);
        }

        $citizens_charter->update($validated);

        return response()->json(['success' => 'Video updated successfully.']);
    }

    public function destroy(Citizens_Charter $citizens_charter)
    {
        if ($citizens_charter->file) {
            Storage::disk('public')->delete($citizens_charter->file);
        }

        if ($citizens_charter->thumbnail) {
            Storage::disk('public')->delete($citizens_charter->thumbnail);
        }

        $citizens_charter->delete();

        return redirect()->route('AdminCitizensCharter')->with('success', 'Video deleted successfully.');
    }

    public function storePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        $existingPdf = Citizens_Charter_PDF::first();
        if ($existingPdf && $existingPdf->file) {
            Storage::disk('public')->delete($existingPdf->file);
            $existingPdf->delete();
        }

        $filePath = $request->file('file')->store('citizens_charters_pdf', 'public');

        Citizens_Charter_PDF::create([
            'file' => $filePath,
        ]);

        return response()->json(['success' => 'PDF uploaded successfully.']);
    }

    public function downloadPdf()
    {
        $pdf = Citizens_Charter_PDF::first();

        if (!$pdf || !$pdf->file) {
            abort(404);
        }

        return Storage::disk('public')->download($pdf->file);
    }
}
