<?php

namespace App\Http\Controllers;

use App\Models\Bohol_Issuance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBoholIssuanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Bohol_Issuance::orderBy('date', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('outcome_area', 'LIKE', "%$search%")
                    ->orWhere('category', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%")
                    ->orWhere('reference_num', 'LIKE', "%$search%");
            });
        }

        $b_issuances = $query->paginate(5)->withQueryString();

        return Inertia::render('Admin/AdminIssuances', [
            'b_issuances' => $b_issuances,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outcome_area' => 'nullable|string',
            'date' => 'nullable|date',
            'category' => 'nullable|string',
            'title' => 'nullable|string',
            'reference_num' => 'nullable|string',
            'file' => 'nullable|mimes:pdf',
        ]);

        $uploadPath = public_path('issuance_files');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('file')) {
            $fileName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        }

        Bohol_Issuance::create($validated);

        return redirect()->route('AdminIssuances')->with('success', 'Added Successfully!');
    }

    public function update(Request $request, Bohol_Issuance $issuances)
    {
        $validated = $request->validate([
            'outcome_area' => 'nullable|string',
            'date' => 'nullable|date',
            'category' => 'nullable|string',
            'title' => 'nullable|string',
            'reference_num' => 'nullable|string',
            'file' => 'nullable|mimes:pdf',
        ]);

        $uploadPath = public_path('issuance_files');

        if ($request->hasFile('file')) {
            if ($issuances->file) {
                $filePath = public_path('issuance_files/' . $issuances->file);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $fileName = Str::random(20) . '.' . $request->file('file')->extension();
            $request->file('file')->move($uploadPath, $fileName);
            $validated['file'] = $fileName;
        }

        $issuances->update($validated);

        return redirect()->route('AdminIssuances')->with('success', 'Updated Successfully!');
    }

    public function destroy(Bohol_Issuance $issuances)
    {
        if ($issuances->file) {
            $filePath = public_path('issuance_files/' . $issuances->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $issuances->delete();

        return redirect()->route('AdminIssuances')->with('success', 'Deleted Successfully!');
    }
}
