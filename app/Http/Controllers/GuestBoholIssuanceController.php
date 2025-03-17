<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bohol_Issuance;
use Inertia\Inertia;

class GuestBoholIssuanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Bohol_Issuance::select('id', 'title', 'reference_num', 'outcome_area', 'file')
            ->orderBy('date', 'DESC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('outcome_area', 'LIKE', "%$search%")
                    ->orWhere('category', 'LIKE', "%$search%")
                    ->orWhere('title', 'LIKE', "%$search%")
                    ->orWhere('reference_num', 'LIKE', "%$search%");
            });
        }

        if ($request->filled('outcome_area')) {
            $query->where('outcome_area', $request->outcome_area);
        }

        $b_issuances = $query->paginate(5)->withQueryString();

        return Inertia::render('Guest/LatestIssuances', [
            'b_issuances' => $b_issuances,
            'filters' => $request->only(['search', 'outcome_area']),
            'outcomeAreas' => [
                "ACCOUNTABLE, TRANSPARENT, PARTICIPATIVE, AND EFFECTIVE LOCAL GOVERNANCE",
                "PEACEFUL, ORDERLY AND SAFE LGUS STRATEGIC PRIORITIES",
                "SOCIALLY PROTECTIVE LGUS",
                "ENVIRONMENT-PROTECTIVE, CLIMATE CHANGE ADAPTIVE AND DISASTER RESILIENT LGUS",
                "BUSINESS-FRIENDLY AND COMPETITIVE LGUS",
                "STRENGTHENING OF INTERNAL GOVERNANCE",
            ],
        ]);
    }
}
