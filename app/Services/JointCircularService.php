<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\JointCircular;
use Illuminate\Support\Facades\Log;
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

                $firstRow = $crawler->filter('table.view_details tr')->first();
                if ($firstRow->count() > 0) {
                    Log::info("First row HTML: " . $firstRow->html());
                }

                $circulars = $crawler->filter('table.view_details tr')->each(function (Crawler $node) use ($client, $search) {
                    try {
                        $title = $node->filter('td a')->count() > 0 ? trim($node->filter('td a')->text()) : null;
                        $link = $node->filter('td a')->count() > 0 ? $node->filter('td a')->attr('href') : null;

                        $referenceNode = $node->filter('td strong');
                        $reference = $referenceNode->count() > 0 ? trim(str_replace('Reference No:', '', $referenceNode->text())) : null;

                        $date = $node->filter('td[nowrap]')->count() > 0 ? trim($node->filter('td[nowrap]')->text()) : null;

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

                        if (!empty($link)) {
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

                                    // Download and save the PDF file
                                    $pdfContent = $client->request('GET', $downloadLink)->getBody()->getContents();

                                    $originalFilename = basename(parse_url($downloadLink, PHP_URL_PATH));
                                    if (empty($originalFilename)) {
                                        $originalFilename = Str::slug($title) . '.pdf';
                                    }

                                    if (!str_ends_with(strtolower($originalFilename), '.pdf')) {
                                        $originalFilename .= '.pdf';
                                    }

                                    $pdfFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalFilename);
                                    $directory = public_path('joint_circulars');

                                    if (!file_exists($directory)) {
                                        mkdir($directory, 0755, true);
                                    }

                                    file_put_contents($directory . '/' . $pdfFilename, $pdfContent);

                                    if (!file_exists($directory . '/' . $pdfFilename)) {
                                        Log::error("Failed to save PDF: " . $directory . '/' . $pdfFilename);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::warning("Failed to fetch/download PDF for {$title}: " . $e->getMessage());
                            }
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
                        Log::warning("Error processing a row: " . $e->getMessage());
                        return null;
                    }
                });

                $circulars = array_filter($circulars);

                foreach ($circulars as $circular) {
                    $uniqueKey = $circular['reference'] ?? md5($circular['title'] . $circular['date']);

                    if (!array_key_exists($uniqueKey, $uniqueCirculars)) {
                        $uniqueCirculars[$uniqueKey] = $circular;

                        $record = JointCircular::firstOrNew(['reference' => $circular['reference'] ?? null]);

                        $record->title = $circular['title'];
                        $record->link = $circular['link'];
                        $record->date = $circular['date'];
                        $record->download_link = $circular['download_link'];
                        $record->file = $circular['file'] ?? null;

                        $record->save();

                        $saved = JointCircular::where('reference', $circular['reference'] ?? null)->first();
                        Log::info("Saved record file value:", ['file' => $saved->file ?? 'null']);
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
                'circulars' => array_values($uniqueCirculars),
            ];
        } catch (\Exception $e) {
            Log::error('Error scraping data: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error scraping data: ' . $e->getMessage(),
                'circulars' => []
            ];
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

        $response = Http::post('https://issuances.dilgbohol.com/webhook/joint-circular', [
            // $response = Http::post('http://127.0.0.1:8000/webhook/joint-circular', [
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
