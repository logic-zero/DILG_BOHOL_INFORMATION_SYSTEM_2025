<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\LegalOpinion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Storage;

class LegalOpinionService
{
    public function scrapeLegalOpinions(string $url, $search = null)
    {
        $client = new Client([
            'timeout' => 60,
            'verify' => storage_path('cacert.pem'),
        ]);
        $allOpinions = [];
        $categories = [];
        $uniqueOpinions = [];

        try {
            $currentPage = 1;

            while ($url) {
                Log::info("Scraping URL (Page {$currentPage}): {$url}");
                $currentPage++;

                $response = $client->request('GET', $url);
                $html = $response->getBody()->getContents();
                $crawler = new Crawler($html);

                // Scrape all rows
                $opinions = $crawler->filter('table.view_details tr')->each(function (Crawler $node) use ($client, $search) {
                    try {
                        $title = $node->filter('td a')->count() > 0 ? $node->filter('td a')->text() : null;
                        $link = $node->filter('td a')->count() > 0 ? $node->filter('td a')->attr('href') : null;
                        $category = $node->filter('td strong span')->count() > 0 ? $node->filter('td strong span')->text() : null;
                        $reference = $node->filter('td strong')->count() > 0 ? $node->filter('td strong')->text() : null;
                        $date = $node->filter('td[nowrap]')->count() > 0 ? $node->filter('td[nowrap]')->text() : null;

                        if ($reference) {
                            $reference = trim(str_replace('Reference No:', '', $reference));
                        }

                        if (!$title || !$link) {
                            return null;
                        }

                        if (!str_starts_with($link, 'http')) {
                            $link = 'https://dilg.gov.ph' . $link;
                        }

                        if ($search && stripos($title, $search) === false && stripos($reference, $search) === false) {
                            return null;
                        }

                        // **Step 2: Scrape the download link from the individual legal opinion page**
                        $downloadLink = null;
                        try {
                            $response = $client->request('GET', $link);
                            $detailHtml = $response->getBody()->getContents();
                            $detailCrawler = new Crawler($detailHtml);

                            // Extract the actual PDF download link
                            $downloadNode = $detailCrawler->filter('a.btn_download');
                            if ($downloadNode->count() > 0) {
                                $downloadLink = $downloadNode->attr('href');

                                if ($downloadLink && !str_starts_with($downloadLink, 'http')) {
                                    $downloadLink = 'https://dilg.gov.ph' . $downloadLink;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to fetch download link for {$title}: " . $e->getMessage());
                        }

                        // **Download and Save PDF if it matches the title condition**
                        $pdfPath = null;
                        if ($downloadLink && (str_starts_with($title, 'LO-') || str_starts_with($title, 'DILG LO No.'))) {
                            try {
                                $pdfContent = $client->request('GET', $downloadLink)->getBody()->getContents();
                                $fileName = str_replace([' ', '/', '\\'], '_', $title) . '.pdf'; // Clean filename
                                $filePath = 'public/legal_opinions/' . $fileName;

                                Storage::put($filePath, $pdfContent);
                                $pdfPath = Storage::url($filePath);

                                Log::info("PDF saved: {$pdfPath}");
                            } catch (\Exception $e) {
                                Log::warning("Failed to download PDF for {$title}: " . $e->getMessage());
                            }
                        }

                        return compact('title', 'link', 'category', 'reference', 'date', 'downloadLink', 'pdfPath');
                    } catch (\Exception $e) {
                        Log::warning("Skipping a row due to error: " . $e->getMessage());
                        return null;
                    }
                });

                $opinions = array_filter($opinions);
                foreach ($opinions as $opinion) {
                    if (!array_key_exists($opinion['reference'], $uniqueOpinions)) {
                        $uniqueOpinions[$opinion['reference']] = $opinion;

                        // **Store the opinion and PDF path in the database**
                        LegalOpinion::updateOrCreate(
                            ['reference' => $opinion['reference']],
                            [
                                'title' => $opinion['title'],
                                'link' => $opinion['link'],
                                'category' => $opinion['category'],
                                'date' => $opinion['date'],
                                'download_link' => $opinion['downloadLink'],
                                'pdf_path' => $opinion['pdfPath'], // Store PDF path
                            ]
                        );
                    }
                }

                if (empty($categories)) {
                    $categories = $crawler->filter('form.myformStyle select.catBox option')->each(function (Crawler $node) {
                        return [
                            'value' => $node->attr('value'),
                            'text' => $node->text(),
                        ];
                    });
                }

                $nextPageNode = $crawler->filter('li.pWord a:contains("next")');
                if ($nextPageNode->count() > 0) {
                    $nextPageHref = $nextPageNode->attr('href');
                    $url = str_starts_with($nextPageHref, 'http') ? $nextPageHref : 'https://dilg.gov.ph' . $nextPageHref;
                } else {
                    $url = null;
                    Log::info('No more pages to scrape.');
                }
            }

            return [
                'opinions' => array_values($uniqueOpinions),
                'categories' => $categories,
            ];
        } catch (\Exception $e) {
            Log::error('Error scraping data: ' . $e->getMessage());
            return ['error' => 'Error scraping data: ' . $e->getMessage()];
        }
    }

    public function sendAllLegalOpinionsToTangkaraw()
    {
        // Fetch data
        $legalOpinions = DB::connection('dilg')->table('legal_opinions')->get();

        if ($legalOpinions->isEmpty()) {
            Log::warning('No legal opinions found in the DILG database.');
            return [
                'success' => false,
                'message' => 'No legal opinions to send.',
            ];
        }

        Log::info('Fetched legal opinions from DILG database:', $legalOpinions->toArray());

        $legalOpinionsData = $legalOpinions->map(function ($legalOpinion) {
            return [
                'title' => $legalOpinion->title,
                'link' => $legalOpinion->link,
                'category' => $legalOpinion->category,
                'reference' => $legalOpinion->reference,
                'date' => $legalOpinion->date,
                'download_link' => $legalOpinion->download_link,
                'extracted_texts' => $legalOpinion->extracted_texts,
            ];
        })->toArray();

        Log::info('Prepared legal opinions to send:', $legalOpinionsData);

        if (empty($legalOpinionsData)) {
            Log::warning('Mapped legal opinions data is empty. Nothing to send.');
            return [
                'success' => false,
                'message' => 'No legal opinions to send.',
            ];
        }

        Log::info('Sending legal opinions to Tangkaraw:', ['payload' => $legalOpinionsData]);

        $response = Http::post('http://127.0.0.1:8000/webhook/legal-opinion', [
            'legal_opinions' => $legalOpinionsData,
        ]);

        Log::info('Response from Tangkaraw:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'All legal opinions sent successfully.',
                'data' => $legalOpinionsData,
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to send legal opinions to Tangkaraw.',
                'error' => $response->body(),
            ];
        }
    }
}