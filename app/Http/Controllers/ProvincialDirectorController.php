<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProvincialDirector;
use Illuminate\Http\Request;
use App\Models\PDMUs;
use Inertia\Inertia;

class ProvincialDirectorController extends Controller
{
    public function index()
    {
        $pdmu = PDMUs::where('position', 'Provincial Director')->first();
        $pd = ProvincialDirector::all();

        return Inertia::render('Guest/ProvincialDirector', [
            'pdmu' => $pdmu,
            'pd' => $pd,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        ProvincialDirector::create($validated);

        return redirect()->route('dashboard')->with('success', "Provincial Director's Message Added Successfully!");
    }

    public function update(Request $request, ProvincialDirector $pd)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $pd->update($validated);

        return redirect()->route('dashboard')->with('success', "Provincial Director's Message Updated Successfully!");
    }
}
