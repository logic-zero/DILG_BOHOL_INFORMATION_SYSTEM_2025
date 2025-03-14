<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestLguController extends Controller
{
    public function index(Request $request)
    {
        $query = Lgu::with('municipality')->orderBy('municipality_id', 'ASC');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mayor', 'LIKE', "%$search%")
                    ->orWhere('vice_mayor', 'LIKE', "%$search%")
                    ->orWhere('sb_member1', 'LIKE', "%$search%")
                    ->orWhere('sb_member2', 'LIKE', "%$search%")
                    ->orWhere('sb_member3', 'LIKE', "%$search%")
                    ->orWhere('sb_member4', 'LIKE', "%$search%")
                    ->orWhere('sb_member5', 'LIKE', "%$search%")
                    ->orWhere('sb_member6', 'LIKE', "%$search%")
                    ->orWhere('sb_member7', 'LIKE', "%$search%")
                    ->orWhere('sb_member8', 'LIKE', "%$search%")
                    ->orWhere('lb_pres', 'LIKE', "%$search%")
                    ->orWhere('psk_pres', 'LIKE', "%$search%")
                    ->orWhereHas('municipality', function ($mq) use ($search) {
                        $mq->where('municipality', 'LIKE', "%$search%");
                    });
            });
        }

        if ($request->filled('municipality_id')) {
            $query->where('municipality_id', $request->municipality_id);
        }

        $lgus = $query->get();
        $municipalities = Municipality::all();

        return Inertia::render('Guest/LGUs', [
            'lgus' => $lgus,
            'municipalities' => $municipalities,
            'filters' => $request->only(['search', 'municipality_id']),
        ]);
    }
}
