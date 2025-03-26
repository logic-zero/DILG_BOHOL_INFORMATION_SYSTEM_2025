<?php

namespace App\Http\Controllers;

use App\Models\Citizens_Charter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestCitizens_CharterController extends Controller
{
    public function index()
    {
        $charters = Citizens_Charter::paginate(1);

        return Inertia::render('Guest/CitizensCharter', [
            'charters' => $charters,
        ]);
    }
}
