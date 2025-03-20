<?php

namespace App\Http\Controllers;

use App\Models\PDMUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminPDMUController extends Controller
{
    public function index(Request $request)
    {
        $query = PDMUs::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('position', 'LIKE', "%$search%");
            });
        }

        $pdmus = $query->get();

        return Inertia::render('Admin/AdminPDMU', [
            'pdmus' => $pdmus,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'fname' => 'required|string',
            'mid_initial' => 'nullable|string',
            'lname' => 'required|string',
            'position' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_img')) {
            $validated['profile_img'] = $request->file('profile_img')->store('pdmus', 'public');
        }

        PDMUs::create($validated);

        return redirect()->route('AdminPDMU')->with('success', 'PDMU added successfully.');
    }

    public function update(Request $request, PDMUs $pdmu)
    {
        $validated = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'fname' => 'required|string',
            'mid_initial' => 'nullable|string',
            'lname' => 'required|string',
            'position' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_img')) {
            if ($pdmu->profile_img) {
                Storage::disk('public')->delete($pdmu->profile_img);
            }
            $validated['profile_img'] = $request->file('profile_img')->store('pdmus', 'public');
        } elseif ($request->has('remove_image')) {
            if ($pdmu->profile_img) {
                Storage::disk('public')->delete($pdmu->profile_img);
            }
            $validated['profile_img'] = null;
        }

        $pdmu->update($validated);

        return redirect()->route('AdminPDMU')->with('success', 'PDMU updated successfully.');
    }

    public function destroy(PDMUs $pdmu)
    {
        if ($pdmu->profile_img) {
            Storage::disk('public')->delete($pdmu->profile_img);
        }

        $pdmu->delete();

        return redirect()->route('AdminPDMU')->with('success', 'PDMU deleted successfully.');
    }
}
