<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GuestNewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::where('status', 1)
                    ->latest()
                    ->when($request->search, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('title', 'like', '%' . $search . '%')
                            ->orWhere('caption', 'like', '%' . $search . '%');
                        });
                    })
                    ->when($request->from_date, function ($query, $fromDate) {
                        $query->whereDate('created_at', '>=', Carbon::parse($fromDate));
                    })
                    ->when($request->to_date, function ($query, $toDate) {
                        $query->whereDate('created_at', '<=', Carbon::parse($toDate));
                    })
                    ->paginate(10)
                    ->withQueryString()
                    ->through(function ($news) {
                        $news->images = json_decode($news->images);
                        return $news;
                    });

        return Inertia::render('Guest/News', [
            'news' => $news,
            'filters' => $request->only(['search', 'from_date', 'to_date']),
        ]);
    }

    public function show($id)
    {
        $news = News::where('status', 1)->findOrFail($id);
        $news->images = json_decode($news->images);

        return Inertia::render('Guest/GuestSingleNewsDetails', [
            'news' => $news,
            'meta' => [
                'title' => $news->title,
                'description' => $news->caption ?: Str::limit(strip_tags($news->content), 160),
                'image' => $news->images ? asset('storage/'.$news->images[0]) : null,
            ]
        ]);
    }
}
