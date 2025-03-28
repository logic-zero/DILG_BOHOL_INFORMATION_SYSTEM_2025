<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScraperService;
use Inertia\Inertia;

class ScraperController extends Controller
{
    protected $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    public function scrapeLegalOpinions()
    {
        $url = 'https://dilg.gov.ph/legal-opinions';
        $search = request('search'); // Optional search term from query string

        $data = $this->scraperService->scrapeLegalOpinions($url, $search);

        return Inertia::render('LegalOpinions/Index', [
            'opinions' => $data['opinions'],
            'categories' => $data['categories'],
            'error' => $data['error'] ?? null,
        ]);
    }
}
