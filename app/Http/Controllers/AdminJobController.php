<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminJobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::orderBy('created_at', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('position', 'LIKE', "%$search%")
                    ->orWhere('details', 'LIKE', "%$search%")
                    ->orWhere('link', 'LIKE', "%$search%")
                    ->orWhere('remarks', 'LIKE', "%$search%");
            });
        }

        $jobs = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/AdminJobVacancies', [
            'jobs' => $jobs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'hiring_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
            'position' => 'required|string',
            'details' => 'required|string',
            'link' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Set default remarks to "Available"
        $validated['remarks'] = $validated['remarks'] ?? 'Available';

        // Handle file upload
        if ($request->hasFile('hiring_img')) {
            $validated['hiring_img'] = $request->file('hiring_img')->store('hiring_images', 'public');
        } else {
            $validated['hiring_img'] = 'default'; // Use a special value for the default image
        }

        Job::create($validated);

        return redirect()->route('AdminJobs')->with('success', 'Job Added Successfully!');
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'hiring_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
            'position' => 'required|string',
            'details' => 'required|string',
            'link' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('hiring_img')) {
            // Delete the old image if it exists and is not the default
            if ($job->hiring_img && $job->hiring_img !== 'default' && Storage::disk('public')->exists($job->hiring_img)) {
                Storage::disk('public')->delete($job->hiring_img);
            }
            $validated['hiring_img'] = $request->file('hiring_img')->store('hiring_images', 'public');
        }

        $job->update($validated);

        return redirect()->route('AdminJobs')->with('success', 'Job Updated Successfully!');
    }

    public function destroy(Job $job)
    {
        // Delete the associated image file if it exists and is not the default
        if ($job->hiring_img && $job->hiring_img !== 'default' && Storage::disk('public')->exists($job->hiring_img)) {
            Storage::disk('public')->delete($job->hiring_img);
        }

        $job->delete();

        return redirect()->route('AdminJobs')->with('success', 'Job Deleted Successfully!');
    }
}
