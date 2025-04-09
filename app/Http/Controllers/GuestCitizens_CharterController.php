<?php

namespace App\Http\Controllers;

use App\Models\Citizens_Charter;
use App\Models\Citizens_Charter_PDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuestCitizens_CharterController extends Controller
{
    public function index()
    {
        $charters = Citizens_Charter::paginate(1);

        $pdf = Citizens_Charter_PDF::first();

        return Inertia::render('Guest/CitizensCharter', [
            'charters' => $charters,
            'pdf' => $pdf,
        ]);
    }

    public function downloadPdf()
    {
        $pdf = Citizens_Charter_PDF::first();

        if (!$pdf || !$pdf->file) {
            abort(404);
        }

        $filePath = public_path('citizens_charters_pdf/' . $pdf->file);
        if (!File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }
}
