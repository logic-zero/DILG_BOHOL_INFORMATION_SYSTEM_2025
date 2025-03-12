<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::where('status', 1)->latest(); // Only approved news

        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('caption', 'like', '%' . $request->search . '%');
            });
        }

        // Apply date filter (From - To)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        // Paginate with 10 items per page
        $news = $query->paginate(10)->withQueryString()->through(function ($news) {
            $news->images = json_decode($news->images);
            return $news;
        });

        return Inertia::render('Guest/News', [
            'news' => $news,
            'filters' => $request->only(['search', 'from_date', 'to_date']),
        ]);
    }
}
