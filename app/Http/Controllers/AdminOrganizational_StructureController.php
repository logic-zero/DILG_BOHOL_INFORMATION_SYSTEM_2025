<?php

namespace App\Http\Controllers;

use App\Models\Oragnizational_Structure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminOrganizational_StructureController extends Controller
{
    public function index(Request $request)
    {
        $query = Oragnizational_Structure::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('position', 'LIKE', "%$search%")
                  ->orWhere('management_and_administrative_roles', 'LIKE', "%$search%");
            });
        }

        $organizationalStructures = $query->get();

        return Inertia::render('Admin/AdminOrgStructure', [
            'organizationalStructures' => $organizationalStructures,
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
            'management_and_administrative_roles' => 'nullable|string',
        ]);

        $uploadPath = public_path('organizational_structure');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('profile_img')) {
            $fileName = Str::random(20) . '.' . $request->file('profile_img')->extension();
            $request->file('profile_img')->move($uploadPath, $fileName);
            $validated['profile_img'] = $fileName;
        }

        Oragnizational_Structure::create($validated);

        return redirect()->route('AdminOrganizationalStructure')->with('success', 'Organizational structure added successfully.');
    }

    public function update(Request $request, Oragnizational_Structure $organizational_structure)
    {
        $validated = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'fname' => 'required|string',
            'mid_initial' => 'nullable|string',
            'lname' => 'required|string',
            'position' => 'nullable|string',
            'management_and_administrative_roles' => 'nullable|string',
        ]);

        $uploadPath = public_path('organizational_structure');

        if ($request->hasFile('profile_img')) {
            if ($organizational_structure->profile_img) {
                $filePath = public_path('organizational_structure/' . $organizational_structure->profile_img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $fileName = Str::random(20) . '.' . $request->file('profile_img')->extension();
            $request->file('profile_img')->move($uploadPath, $fileName);
            $validated['profile_img'] = $fileName;
        } elseif ($request->has('remove_image')) {
            if ($organizational_structure->profile_img) {
                $filePath = public_path('organizational_structure/' . $organizational_structure->profile_img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $validated['profile_img'] = null;
        }

        $organizational_structure->update($validated);

        return redirect()->route('AdminOrganizationalStructure')->with('success', 'Organizational structure updated successfully.');
    }

    public function destroy(Oragnizational_Structure $organizational_structure)
    {
        if ($organizational_structure->profile_img) {
            $filePath = public_path('organizational_structure/' . $organizational_structure->profile_img);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $organizational_structure->delete();

        return redirect()->route('AdminOrganizationalStructure')->with('success', 'Organizational structure deleted successfully.');
    }
}
