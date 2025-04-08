<?php

namespace App\Http\Controllers;

use App\Models\Field_Officer;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminField_OfficersController extends Controller
{
    public function index(Request $request)
    {
        $query = Field_Officer::with('municipality');

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('position', 'LIKE', "%$search%");
            });
        }

        $fieldOfficers = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/AdminFieldOfficers', [
            'fieldOfficers' => $fieldOfficers,
            'filters' => $request->only(['search', 'municipality_id']),
            'municipalities' => Municipality::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'municipality_id' => 'required|exists:municipalities,id',
            'fname' => 'required|string',
            'mid_initial' => 'nullable|string',
            'lname' => 'required|string',
            'position' => 'required|string',
            'cluster' => 'nullable|string',
        ]);

        $uploadPath = public_path('field_officers');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('profile_img')) {
            $fileName = Str::random(20) . '.' . $request->file('profile_img')->extension();
            $request->file('profile_img')->move($uploadPath, $fileName);
            $validated['profile_img'] = $fileName;
        }

        Field_Officer::create($validated);

        return redirect()->route('AdminFieldOfficers')->with('success', 'Field officer added successfully.');
    }

    public function update(Request $request, Field_Officer $field_officer)
    {
        $validated = $request->validate([
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'municipality_id' => 'required|exists:municipalities,id',
            'fname' => 'required|string',
            'mid_initial' => 'nullable|string',
            'lname' => 'required|string',
            'position' => 'required|string',
            'cluster' => 'nullable|string',
        ]);

        $uploadPath = public_path('field_officers');

        if ($request->hasFile('profile_img')) {
            if ($field_officer->profile_img) {
                $filePath = public_path('field_officers/' . $field_officer->profile_img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $fileName = Str::random(20) . '.' . $request->file('profile_img')->extension();
            $request->file('profile_img')->move($uploadPath, $fileName);
            $validated['profile_img'] = $fileName;
        } elseif ($request->has('remove_image')) {
            if ($field_officer->profile_img) {
                $filePath = public_path('field_officers/' . $field_officer->profile_img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $validated['profile_img'] = null;
        }

        $field_officer->update($validated);

        return redirect()->route('AdminFieldOfficers')->with('success', 'Field officer updated successfully.');
    }

    public function destroy(Field_Officer $field_officer)
    {
        if ($field_officer->profile_img) {
            $filePath = public_path('field_officers/' . $field_officer->profile_img);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $field_officer->delete();

        return redirect()->route('AdminFieldOfficers')->with('success', 'Field officer deleted successfully.');
    }
}
