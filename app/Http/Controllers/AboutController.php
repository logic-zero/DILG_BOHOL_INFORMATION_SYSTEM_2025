<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function index()
    {
        $audio = Audio::latest()->first();

        return Inertia::render('Guest/AboutUs', [
            'audio' => $audio,
        ]);
    }
}
