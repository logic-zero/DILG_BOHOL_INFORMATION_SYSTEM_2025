<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    // Display the news list
    public function index(Request $request)
    {
        $query = News::with('user')->latest();

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('caption', 'like', '%' . $request->search . '%');
        }

        // Status filter (approved, pending, or both)
        if ($request->has('status') && in_array($request->status, ['approved', 'pending'])) {
            $status = $request->status === 'approved' ? 1 : 0;
            $query->where('status', $status);
        }

        $news = $query->get()->map(function ($news) {
            $news->images = json_decode($news->images);
            return $news;
        });

        return Inertia::render('Admin/AdminNews', [
            'news' => $news,
            'filters' => $request->only(['search', 'status']) // Persist filters
        ]);
    }


    // Store a new news post
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'caption' => 'required|string',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            $imagePaths[] = $image->store('news_images', 'public'); // Store in storage/app/public/news_images
        }

        News::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'images' => json_encode($imagePaths), // Store images as JSON
            'status' => false,
        ]);

        return redirect()->route('AdminNews')->with('success', 'News added successfully.');
    }

    // Update an existing news post
    public function update(Request $request, News $news)
    {
        if (Auth::id() !== $news->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'caption' => 'required|string',
            'images' => 'nullable|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePaths = json_decode($news->images, true) ?? [];

        // Check if existing images were passed
        if ($request->has('existing_images')) {
            $imagePaths = json_decode($request->input('existing_images'), true);
        }

        // Delete old images if new ones are uploaded
        if ($request->hasFile('images')) {
            foreach ($imagePaths as $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('news_images', 'public');
            }
        }

        $news->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'images' => json_encode($imagePaths),
        ]);

        return redirect()->route('AdminNews')->with('success', 'News updated successfully.');
    }

    // Delete a news post
    public function destroy(News $news)
    {
        // Ensure only the owner can delete
        if (Auth::id() !== $news->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete stored images
        foreach (json_decode($news->images, true) as $image) {
            Storage::disk('public')->delete($image);
        }

        $news->delete();

        return redirect()->route('AdminNews')->with('success', 'News deleted successfully.');
    }

    // Toggle the status (Approved / Waiting)
    public function toggleStatus(News $news)
    {
        $news->update(['status' => !$news->status]);

        return redirect()->route('AdminNews')->with('success', 'Status updated.');
    }
}
