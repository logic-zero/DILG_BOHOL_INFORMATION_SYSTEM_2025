<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProvincialDirector;
use App\Models\Oragnizational_Structure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProvincialDirectorController extends Controller
{
    public function index()
    {
        return Inertia::render('Guest/ProvincialDirector', [
            'pdinfo' => Oragnizational_Structure::where('position', 'Provincial Director')->first(),
            'pd' => ProvincialDirector::first(),
        ]);
    }
}
