<?php

namespace App\Services;

use App\Models\RepublicAct;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RepublicActService
{
    /**
     * Scrape the provided URL for legal opinions.
     *
     * @param string $url The URL to scrape.
     * @param string|null $search Optional search term to filter results.
     * @return array An array of legal opinions (titles, links, references, and dates).
     */
    public function scrapeRepublicActs(string $url, $search = null)
    {
        $client = new Client([
            'timeout' => 60,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ],
            'verify' => storage_path('cacert.pem'),
        ]);

        $uniqueActs = [];

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

                $firstRow = $crawler->filter('table.view_details tr')->first();
                if ($firstRow->count() > 0) {
                    Log::info("First row HTML: " . $firstRow->html());
                }

                $acts = $crawler->filter('table.view_details tr')->each(function (Crawler $node) use ($client, $search) {
                    try {
                        $title = $node->filter('td a')->count() > 0 ? $node->filter('td a')->text() : null;
                        $link = $node->filter('td a')->count() > 0 ? $node->filter('td a')->attr('href') : null;
                        $reference = $node->filter('td strong')->count() > 0 ? $node->filter('td strong')->text() : null;
                        $date = $node->filter('td[nowrap]')->count() > 0 ? $node->filter('td[nowrap]')->text() : null;

                        if ($reference) {
                            $reference = trim(str_replace('Reference No:', '', $reference));
                        }

                        if (!$title || !$link) {
                            Log::warning("Skipping row due to missing title or link: " . $node->html());
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
                                $directory = public_path('republic_acts');

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

                $acts = array_filter($acts);
                foreach ($acts as $act) {
                    if (!array_key_exists($act['reference'], $uniqueActs)) {
                        $uniqueActs[$act['reference']] = $act;

                        $fileValue = $act['file'] ?? null;

                        $record = RepublicAct::firstOrNew(['reference' => $act['reference']]);

                        $record->title = $act['title'];
                        $record->link = $act['link'];
                        $record->date = $act['date'];
                        $record->download_link = $act['download_link'];
                        $record->file = $fileValue;

                        $record->save();

                        $saved = RepublicAct::where('reference', $act['reference'])->first();
                        Log::info("Saved record file value:", ['file' => $saved->file]);
                    }
                }

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
                'success' => true,
                'acts' => array_values($uniqueActs),
            ];
        } catch (\Exception $e) {
            Log::error('Error scraping data: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error scraping data: ' . $e->getMessage(),
                'acts' => []
            ];
        }
    }

    public function sendAllRepublicActsToTangkaraw()
    {
        $republicActs = DB::connection('dilg')->table('republic_acts')->get();

        if ($republicActs->isEmpty()) {
            Log::warning('No republic acts found in the DILG database.');
            session()->flash('error', 'No republic acts to send.');
            return;
        }

        Log::info('Fetched republic acts from DILG database:', $republicActs->toArray());

        $republicActsData = $republicActs->map(function ($republicAct) {
            return [
                'title' => $republicAct->title,
                'link' => $republicAct->link,
                'reference' => $republicAct->reference,
                'date' => $republicAct->date,
                'download_link' => $republicAct->download_link,
            ];
        })->toArray();

        Log::info('Prepared republic acts to send:', $republicActsData);

        if (empty($republicActsData)) {
            Log::warning('Mapped republic acts data is empty. Nothing to send.');
            session()->flash('error', 'No republic acts to send.');
            return;
        }

        // Send to Tangkaraw
        Log::info('Sending republic acts to Tangkaraw:', ['payload' => $republicActsData]);

        $response = Http::post('http://127.0.0.1:8000/webhook/republic-act', [
            'republic_acts' => $republicActsData,
        ]);

        Log::info('Response from Tangkaraw:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            session()->flash('message', 'All republic acts sent successfully');
        } else {
            session()->flash('error', 'Failed to send republic acts to Tangkaraw');
        }
    }
}