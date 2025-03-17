<?php

namespace App\Http\Controllers;

use App\Models\Bohol_Issuance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('issuance_files'), $filename);
            $validated['file'] = $filename;
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

        if ($request->hasFile('file')) {
            $destination = public_path('issuance_files/' . $issuances->file);
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('issuance_files'), $filename);
            $validated['file'] = $filename;
        }

        $issuances->update($validated);

        return redirect()->route('AdminIssuances')->with('success', 'Updated Successfully!');
    }

    public function destroy(Bohol_Issuance $issuances)
    {
        $destination = public_path('issuance_files/' . $issuances->file);
        if (File::exists($destination)) {
            File::delete($destination);
        }

        $issuances->delete();

        return redirect()->route('AdminIssuances')->with('success', 'Deleted Successfully!');
    }
}
