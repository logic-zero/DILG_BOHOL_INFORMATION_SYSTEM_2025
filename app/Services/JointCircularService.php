<?php

namespace App\Services;

use App\Models\JointCircular;
use App\Models\PresidentialDirective;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class JointCircularService
{
    /**
     * Scrape the provided URL for legal opinions.
     *
     * @param string $url The URL to scrape.
     * @param string|null $search Optional search term to filter results.
     * @return array An array of legal opinions (titles, links, references, and dates).
     */
    public function scrapeJointCirculars(string $url, $search = null)
    {
        $client = new Client([
            'timeout' => 180,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ],
            'verify' => storage_path('cacert.pem'),
        ]);

        $uniqueCirculars = [];

        try {
            $currentPage = 1;

            while ($url) {
                Log::info("Scraping URL (Page {$currentPage}): {$url}");
                $currentPage++;

                $response = $client->request('GET', $url);
                $html = $response->getBody()->getContents();
                $crawler = new Crawler($html);

                Log::info("Full HTML content: " . substr($html, 0, 500));

                if ($crawler->filter('table.view_details')->count() === 0) {
                    Log::warning("Table 'view_details' not found on page.");
                    break;
                }

                // Log first row HTML
                $firstRow = $crawler->filter('table.view_details tr')->first();
                if ($firstRow->count() > 0) {
                    Log::info("First row HTML: " . $firstRow->html());
                }

                $circulars = $crawler->filter('table.view_details tr')->each(function (Crawler $node) use ($client, $search) {
                    try {
                        $data = [
                            'title' => '',
                            'link' => '',
                            'reference' => '', // Will remain empty if not found
                            'date' => '',
                            'downloadLink' => ''
                        ];

                        // Get title and link (optional)
                        $anchor = $node->filter('td a');
                        $data['title'] = $anchor->count() > 0 ? trim($anchor->text()) : '';
                        $data['link'] = $anchor->count() > 0 ? $anchor->attr('href') : '';

                        // Reference - only set if found, otherwise stays empty
                        $referenceNode = $node->filter('td strong');
                        if ($referenceNode->count() > 0) {
                            $referenceText = trim($referenceNode->text());
                            $data['reference'] = trim(str_replace('Reference No:', '', $referenceText));
                        }
                        // Else: $data['reference'] remains ''

                        // Date (optional)
                        $dateNode = $node->filter('td[nowrap]');
                        $data['date'] = $dateNode->count() > 0 ? trim($dateNode->text()) : '';

                        if ($data['link'] && !str_starts_with($data['link'], 'http')) {
                            $data['link'] = 'https://dilg.gov.ph' . $data['link'];
                        }

                        if (
                            $search &&
                            stripos($data['title'], $search) === false &&
                            stripos($data['reference'], $search) === false
                        ) {
                            return null;
                        }

                        if (!empty($data['link'])) {
                            try {
                                $response = $client->request('GET', $data['link']);
                                $detailHtml = $response->getBody()->getContents();
                                $detailCrawler = new Crawler($detailHtml);

                                $downloadNode = $detailCrawler->filter('a.btn_download');
                                if ($downloadNode->count() > 0) {
                                    $data['downloadLink'] = $downloadNode->attr('href');
                                    if ($data['downloadLink'] && !str_starts_with($data['downloadLink'], 'http')) {
                                        $data['downloadLink'] = 'https://dilg.gov.ph' . $data['downloadLink'];
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("Failed to fetch download link for {$data['title']}: " . $e->getMessage());
                            }
                        }

                        return $data;
                    } catch (\Exception $e) {
                        Log::warning("Error processing a row: " . $e->getMessage());
                        return null;
                    }
                });

                $circulars = array_filter($circulars);

                foreach ($circulars as $circular) {
                    // Use reference if available, otherwise null (won't create duplicates)
                    $uniqueKey = $circular['reference'] ?: null;

                    if ($uniqueKey === null || !array_key_exists($uniqueKey, $uniqueCirculars)) {
                        if ($uniqueKey === null) {
                            // If no reference, just add to array with numeric index
                            $uniqueCirculars[] = $circular;
                        } else {
                            $uniqueCirculars[$uniqueKey] = $circular;
                        }

                        JointCircular::updateOrCreate(
                            ['reference' => $circular['reference'] ?: null], // Use NULL if empty
                            [
                                'title' => $circular['title'],
                                'link' => $circular['link'],
                                'date' => $circular['date'],
                                'download_link' => $circular['downloadLink'],
                            ]
                        );
                    }
                }

                // **Improved Pagination Handling**
                $nextPageNode = $crawler->filter('li.pWord a:contains("next")');
                if ($nextPageNode->count() > 0) {
                    $nextPageHref = $nextPageNode->attr('href');
                    if ($nextPageHref) {
                        $url = str_starts_with($nextPageHref, 'http') ? $nextPageHref : 'https://dilg.gov.ph' . $nextPageHref;
                    } else {
                        Log::info('Next page link found, but no valid href attribute.');
                        break;
                    }
                } else {
                    $url = null;
                    Log::info('No more pages to scrape.');
                }
            }

            return [
                'circulars' => array_values($uniqueCirculars),
            ];
        } catch (\Exception $e) {
            Log::error('Error scraping data: ' . $e->getMessage());
            return ['error' => 'Error scraping data: ' . $e->getMessage()];
        }
    }

    public function sendJointCircularsToTangkaraw()
    {
        $jointCirculars = DB::connection('dilg')->table('joint_circulars')->get();

        if ($jointCirculars->isEmpty()) {
            Log::warning('No joint circulars found in the DILG database.');
            session()->flash('error', 'No joint circulars to send.');
            return;
        }

        Log::info('Fetched joint circulars from DILG database:', $jointCirculars->toArray());

        $jointCircularsData = $jointCirculars->map(function ($jointCircular) {
            return [
                'title' => $jointCircular->title,
                'link' => $jointCircular->link,
                'reference' => $jointCircular->reference,
                'date' => $jointCircular->date,
                'download_link' => $jointCircular->download_link,
            ];
        })->toArray();

        Log::info('Prepared joint circulars to send:', $jointCircularsData);

        if (empty($jointCircularsData)) {
            Log::warning('Mapped joint circulars data is empty. Nothing to send.');
            session()->flash('error', 'No joint circulars to send.');
            return;
        }

        // Send to Tangkaraw
        Log::info('Sending joint circulars to Tangkaraw:', ['payload' => $jointCircularsData]);

        $response = Http::post('http://127.0.0.1:8000/webhook/joint-circular', [
            'joint_circulars' => $jointCircularsData,
        ]);

        Log::info('Response from Tangkaraw:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            session()->flash('message', 'All joint circulars sent successfully');
        } else {
            session()->flash('error', 'Failed to send joint circulars to Tangkaraw');
        }
    }
}
