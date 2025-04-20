<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
            'hiring_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
            'position' => 'required|string',
            'details' => 'required|string',
            'link' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $validated['remarks'] = $validated['remarks'] ?? 'Available';
        $uploadPath = public_path('hiring_images');
        File::ensureDirectoryExists($uploadPath);

        if ($request->hasFile('hiring_img')) {
            $fileName = Str::random(20) . '.' . $request->file('hiring_img')->extension();
            $request->file('hiring_img')->move($uploadPath, $fileName);
            $validated['hiring_img'] = $fileName;
        } else {
            $validated['hiring_img'] = 'default';
        }

        Job::create($validated);

        return redirect()->route('AdminJobs')->with('success', 'Job Added Successfully!');
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'hiring_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'position' => 'required|string',
            'details' => 'required|string',
            'link' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $uploadPath = public_path('hiring_images');

        if ($request->hasFile('hiring_img')) {
            if ($job->hiring_img && $job->hiring_img !== 'default') {
                $filePath = public_path('hiring_images/' . $job->hiring_img);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
            }
            $fileName = Str::random(20) . '.' . $request->file('hiring_img')->extension();
            $request->file('hiring_img')->move($uploadPath, $fileName);
            $validated['hiring_img'] = $fileName;
        }

        $job->update($validated);

        return redirect()->route('AdminJobs')->with('success', 'Job Updated Successfully!');
    }

    public function destroy(Job $job)
    {
        if ($job->hiring_img && $job->hiring_img !== 'default') {
            $filePath = public_path('hiring_images/' . $job->hiring_img);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $job->delete();

        return redirect()->route('AdminJobs')->with('success', 'Job Deleted Successfully!');
    }
}
