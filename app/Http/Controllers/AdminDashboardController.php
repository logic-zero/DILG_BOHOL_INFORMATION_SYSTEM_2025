<?php

namespace App\Http\Controllers;

use App\Models\Bohol_Issuance;
use App\Models\Citizens_Charter;
use App\Models\Faq;
use App\Models\Field_Officer;
use App\Models\Lgu;
use App\Models\News;
use App\Models\Oragnizational_Structure;
use App\Models\PDMUs;
use App\Models\Provincial_Official;
use App\Models\User;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'newsStats' => [
                'approved' => News::where('status', 1)->count(),
                'pending' => News::where('status', 0)->count(),
                'total' => News::count(),
            ],
            'organizational_structure' => Oragnizational_Structure::count(),
            'pdmu' => PDMUs::count(),
            'field_officers' => Field_Officer::count(),
            'lgu' => Lgu::count(),
            'faq' => Faq::count(),
            'issuances' => Bohol_Issuance::count(),
            'prov_officials' => Provincial_Official::count(),
            'citizens_charter' => Citizens_Charter::count(),
            'users' => User::count(),
        ]);
    }
}
