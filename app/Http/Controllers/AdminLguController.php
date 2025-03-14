<?php

namespace App\Http\Controllers;

use App\Models\Lgu;
use App\Models\Municipality;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLguController extends Controller
{
    public function index(Request $request)
    {
        $query = Lgu::with('municipality')->orderBy('municipality_id', 'ASC');

        if ($request->filled('lgus')) {
            $search = $request->lgus;
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
                    ->orWhere('psk_pres', 'LIKE', "%$search%");
            });
        }

        $lgus = $query->paginate(12)->withQueryString();
        $municipalities = Municipality::all();

        return Inertia::render('Admin/AdminLGUs', [
            'lgus' => $lgus,
            'municipalities' => $municipalities,
            'filters' => $request->only(['lgus']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'municipality_id' => 'required|exists:municipalities,id',
            'mayor' => 'required|string|max:255',
            'vice_mayor' => 'required|string|max:255',
            'sb_member1' => 'nullable|string|max:255',
            'sb_member2' => 'nullable|string|max:255',
            'sb_member3' => 'nullable|string|max:255',
            'sb_member4' => 'nullable|string|max:255',
            'sb_member5' => 'nullable|string|max:255',
            'sb_member6' => 'nullable|string|max:255',
            'sb_member7' => 'nullable|string|max:255',
            'sb_member8' => 'nullable|string|max:255',
            'sb_member9' => 'nullable|string|max:255',
            'sb_member10' => 'nullable|string|max:255',
            'lb_pres' => 'nullable|string|max:255',
            'psk_pres' => 'nullable|string|max:255',
        ]);

        Lgu::create($request->all());

        return redirect()->route('AdminLGUs')->with('success', 'Added Successfully!');
    }

    public function update(Request $request, Lgu $lgu)
    {
        $request->validate([
            'mayor' => 'nullable|string|max:255',
            'vice_mayor' => 'nullable|string|max:255',
            'sb_member1' => 'nullable|string|max:255',
            'sb_member2' => 'nullable|string|max:255',
            'sb_member3' => 'nullable|string|max:255',
            'sb_member4' => 'nullable|string|max:255',
            'sb_member5' => 'nullable|string|max:255',
            'sb_member6' => 'nullable|string|max:255',
            'sb_member7' => 'nullable|string|max:255',
            'sb_member8' => 'nullable|string|max:255',
            'sb_member9' => 'nullable|string|max:255',
            'sb_member10' => 'nullable|string|max:255',
            'lb_pres' => 'nullable|string|max:255',
            'psk_pres' => 'nullable|string|max:255',
        ]);

        $lgu->update($request->all());

        return redirect()->route('AdminLGUs')->with('success', 'Updated Successfully!');
    }

    public function destroy(Lgu $lgu)
    {
        $lgu->delete();

        return redirect()->route('AdminLGUs')->with('success', 'Deleted Successfully!');
    }
}
