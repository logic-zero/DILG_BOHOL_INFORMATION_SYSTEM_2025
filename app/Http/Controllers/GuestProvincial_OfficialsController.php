<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Provincial_Official;
use Illuminate\Http\Request;

class GuestProvincial_OfficialsController extends Controller
{
    public function index()
    {
        $officials = Provincial_Official::all();

        $governor = $officials->where('position', 'Governor')->values();
        $viceGovernor = $officials->where('position', 'Vice Governor')->values();
        $firstDistrict = $officials->where('position', 'Member, 1st District')->values();
        $secondDistrict = $officials->where('position', 'Member, 2nd District')->values();
        $thirdDistrict = $officials->where('position', 'Member, 3rd District')->values();
        $exOfficio = $officials->whereIn('position', [
            'President PCL Bohol Federation',
            'Liga ng mga Barangay',
            'SK Federation President',
        ])->values();

        return Inertia::render('Guest/ProvincialOfficials', [
            'governor' => $governor,
            'viceGovernor' => $viceGovernor,
            'firstDistrict' => $firstDistrict,
            'secondDistrict' => $secondDistrict,
            'thirdDistrict' => $thirdDistrict,
            'exOfficio' => $exOfficio,
        ]);
    }
}
