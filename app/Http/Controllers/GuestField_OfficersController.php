<?php

namespace App\Http\Controllers;

use App\Models\Field_Officer;
use App\Models\Municipality;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GuestField_OfficersController extends Controller
{
    public function index(Request $request)
{
    $query = Field_Officer::with('municipality');

    if ($request->filled('municipality_id')) {
        $query->where('municipality_id', $request->municipality_id);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('fname', 'LIKE', "%$search%")
              ->orWhere('lname', 'LIKE', "%$search%")
              ->orWhere('position', 'LIKE', "%$search%");
        });
    }

    $fieldOfficers = $query->get();

    return Inertia::render('Guest/FieldOfficers', [
        'fieldOfficers' => $fieldOfficers,
        'filters' => $request->only(['search', 'municipality_id']),
        'municipalities' => Municipality::all(),
    ]);
}
}
