<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Oragnizational_Structure;
use App\Models\PDMUs;

class GuestOrganizational_StructureController extends Controller
{
    public function index()
{
    $organizationalStructures = Oragnizational_Structure::all();

    $pdmus = PDMUs::where('position', '!=', 'Provincial Director')->get();

    $excludedRoles = [
        'Program Coordinators',
        'Cluster Head, D\'One',
        'Cluster Head, M&M',
        'Program Manager',
        'Admin Services',
    ];

    $groupedData = $organizationalStructures->groupBy('management_and_administrative_roles');

    $nonGroupData = $organizationalStructures->filter(function ($member) use ($excludedRoles) {
        return !in_array($member->management_and_administrative_roles, $excludedRoles) &&
               $member->position !== 'Provincial Director';
    })->values();

    $provincialDirector = $organizationalStructures->where('position', 'Provincial Director')->first();

    $pdmuProvincialDirector = PDMUs::where('position', 'Provincial Director')->first();

    return Inertia::render('Guest/OrganizationalStructure', [
        'provincialDirector' => $provincialDirector,
        'pdmuProvincialDirector' => $pdmuProvincialDirector,
        'groupedData' => $groupedData,
        'nonGroupData' => $nonGroupData,
        'pdmus' => $pdmus,
    ]);
}
}
