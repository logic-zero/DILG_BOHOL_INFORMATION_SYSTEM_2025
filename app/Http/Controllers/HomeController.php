<?php

namespace App\Http\Controllers;

use App\Models\Home_Image;
use Illuminate\Http\Request;
use App\Models\Bohol_Issuance;
use App\Models\News;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function Index()
    {
        $homeImages = Home_Image::first();

        $images = [];
        if ($homeImages) {
            $images = json_decode($homeImages->images, true);
        }

        $news = News::where('status', 1)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($news) {
                $news->images = json_decode($news->images);
                return $news;
            });

        $b_issuances = Bohol_Issuance::select('id', 'title', 'reference_num', 'outcome_area', 'file')
            ->orderBy('date', 'DESC')
            ->take(5)
            ->get();

        return Inertia::render('Guest/Home', [
            'images' => $images,
            'news' => $news,
            'b_issuances' => $b_issuances,
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

}
