<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Services\ScraperService;
use App\Services\RepublicActService;
use App\Services\PresidentialDirectiveService;
use App\Services\LegalOpinionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Resolve the Schedule class from the service container
        $schedule = $this->app->make(Schedule::class);

        // Define your scheduled tasks here
        $schedule->call(function () {
            $republicActService = app(RepublicActService::class);
            $republicActService->scrapeRepublicActs('https://dilg.gov.ph/issuances-archive/ra/');
            $republicActService->sendAllRepublicActsToTangkaraw();

            $presidentialdirectiveService = app(PresidentialDirectiveService::class);
            $presidentialdirectiveService->scrapePresidentialdirectives('https://dilg.gov.ph/issuances-archive/pd/');
            $presidentialdirectiveService->sendPresidentialDirectivesToTangkaraw();

            $scraperService = app(LegalOpinionService::class);
            $scraperService->scrapeLegalOpinions('https://dilg.gov.ph/legal-opinions-archive/');
            $scraperService->sendAllLegalOpinionsToTangkaraw();
        })->everySixHours();
    }
}


// $sendLegalOpinions = app(LegalOpinionService::class);
// $sendLegalOpinions->sendAllLegalOpinionsToTangkaraw();
