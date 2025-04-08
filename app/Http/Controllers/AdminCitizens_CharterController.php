<?php

namespace App\Http\Controllers;

use App\Models\Citizens_Charter;
use App\Models\Citizens_Charter_PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

        $videoPath = public_path('citizens_charters');
        $thumbnailPath = public_path('citizens_charters/thumbnails');
        File::ensureDirectoryExists($videoPath);
        File::ensureDirectoryExists($thumbnailPath);

        if ($request->hasFile('file')) {
            $videoName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($videoPath, $videoName);
            $validated['file'] = $videoName;
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailName = Str::random(20) . '.' . $request->file('thumbnail')->extension();
            $request->file('thumbnail')->move($thumbnailPath, $thumbnailName);
            $validated['thumbnail'] = $thumbnailName;
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

        $videoPath = public_path('citizens_charters');
        $thumbnailPath = public_path('citizens_charters/thumbnails');

        if ($request->hasFile('file')) {
            if ($citizens_charter->file) {
                $oldFilePath = public_path('citizens_charters/' . $citizens_charter->file);
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }
            }
            $videoName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($videoPath, $videoName);
            $validated['file'] = $videoName;
        } else {
            unset($validated['file']);
        }

        if ($request->hasFile('thumbnail')) {
            if ($citizens_charter->thumbnail) {
                $oldThumbPath = public_path('citizens_charters/thumbnails/' . $citizens_charter->thumbnail);
                if (File::exists($oldThumbPath)) {
                    File::delete($oldThumbPath);
                }
            }
            $thumbnailName = Str::random(20) . '.' . $request->file('thumbnail')->extension();
            $request->file('thumbnail')->move($thumbnailPath, $thumbnailName);
            $validated['thumbnail'] = $thumbnailName;
        } else {
            unset($validated['thumbnail']);
        }

        $citizens_charter->update($validated);

        return response()->json(['success' => 'Video updated successfully.']);
    }

    public function destroy(Citizens_Charter $citizens_charter)
    {
        if ($citizens_charter->file) {
            $filePath = public_path('citizens_charters/' . $citizens_charter->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        if ($citizens_charter->thumbnail) {
            $thumbPath = public_path('citizens_charters/thumbnails/' . $citizens_charter->thumbnail);
            if (File::exists($thumbPath)) {
                File::delete($thumbPath);
            }
        }

        $citizens_charter->delete();

        return redirect()->route('AdminCitizensCharter')->with('success', 'Video deleted successfully.');
    }

    public function storePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:51200',
        ]);

        $pdfPath = public_path('citizens_charters_pdf');
        File::ensureDirectoryExists($pdfPath);

        $existingPdf = Citizens_Charter_PDF::first();
        if ($existingPdf && $existingPdf->file) {
            $oldPdfPath = public_path('citizens_charters_pdf/' . $existingPdf->file);
            if (File::exists($oldPdfPath)) {
                File::delete($oldPdfPath);
            }
            $existingPdf->delete();
        }

        $fileName = Str::random(20) . '.' . $request->file('file')->extension();
        $request->file('file')->move($pdfPath, $fileName);

        Citizens_Charter_PDF::create([
            'file' => $fileName,
        ]);

        return response()->json(['success' => 'PDF uploaded successfully.']);
    }

    public function downloadPdf()
    {
        $pdf = Citizens_Charter_PDF::first();

        if (!$pdf || !$pdf->file) {
            abort(404);
        }

        $filePath = public_path('citizens_charters_pdf/' . $pdf->file);
        if (!File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }
}
