<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
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

                        $downloadLink = null;
                        $pdfFilename = null;

                        try {
                            $response = $client->request('GET', $link);
                            $detailHtml = $response->getBody()->getContents();
                            $detailCrawler = new Crawler($detailHtml);

                            $downloadNode = $detailCrawler->filter('a.btn_download');
                            if ($downloadNode->count() > 0) {
                                $downloadLink = $downloadNode->attr('href');

                                if ($downloadLink && !str_starts_with($downloadLink, 'http')) {
                                    $downloadLink = 'https://dilg.gov.ph' . $downloadLink;
                                }
                                $pdfContent = $client->request('GET', $downloadLink)->getBody()->getContents();

                                $originalFilename = basename(parse_url($downloadLink, PHP_URL_PATH));
                                if (empty($originalFilename)) {
                                    $originalFilename = Str::slug($title) . '.pdf';
                                }

                                if (!str_ends_with(strtolower($originalFilename), '.pdf')) {
                                    $originalFilename .= '.pdf';
                                }

                                $pdfFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalFilename);
                                $directory = public_path('legal_opinions');

                                if (!file_exists($directory)) {
                                    mkdir($directory, 0755, true);
                                }

                                file_put_contents($directory . '/' . $pdfFilename, $pdfContent);

                                if (!file_exists($directory . '/' . $pdfFilename)) {
                                    Log::error("Failed to save PDF: " . $directory . '/' . $pdfFilename);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to fetch download link for {$title}: " . $e->getMessage());
                        }
                        return [
                            'title' => $title,
                            'link' => $link,
                            'category' => $category,
                            'reference' => $reference,
                            'date' => $date,
                            'download_link' => $downloadLink,
                            'file' => $pdfFilename
                        ];
                    } catch (\Exception $e) {
                        Log::warning("Skipping a row due to error: " . $e->getMessage());
                        return null;
                    }
                });

                $opinions = array_filter($opinions);
                foreach ($opinions as $opinion) {
                    if (!array_key_exists($opinion['reference'], $uniqueOpinions)) {
                        $uniqueOpinions[$opinion['reference']] = $opinion;


                        $fileValue = $opinion['file'] ?? null;

                        $record = LegalOpinion::firstOrNew(['reference' => $opinion['reference']]);

                        $record->title = $opinion['title'];
                        $record->link = $opinion['link'];
                        $record->date = $opinion['date'];
                        $record->download_link = $opinion['download_link'];
                        $record->file = $fileValue;

                        $record->save();

                        $saved = LegalOpinion::where('reference', $opinion['reference'])->first();
                        Log::info("Saved record file value:", ['file' => $saved->file]);

                        // LegalOpinion::updateOrCreate(
                        //     ['reference' => $opinion['reference']],
                        //     [
                        //         'title' => $opinion['title'],
                        //         'link' => $opinion['link'],
                        //         'category' => $opinion['category'],
                        //         'date' => $opinion['date'],
                        //         'download_link' => $opinion['downloadLink'],
                        //         'pdf_path' => $opinion['pdfPath'], // Store PDF path
                        //     ]
                        // );
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
                'success' => true,
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

        $response = Http::post('https://issuances.dilgbohol.com/webhook/legal-opinion', [
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