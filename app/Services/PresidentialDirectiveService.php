<?php

namespace App\Services;

use App\Models\PresidentialDirective;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PresidentialDirectiveService
{
    /**
     * Scrape the provided URL for legal opinions.
     *
     * @param string $url The URL to scrape.
     * @param string|null $search Optional search term to filter results.
     * @return array An array of legal opinions (titles, links, references, and dates).
     */

    public function scrapePresidentialdirectives(string $url, $search = null)
    {
        $client = new Client([
            'timeout' => 60,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ],
            'verify' => storage_path('cacert.pem'),
        ]);

        $uniqueDirectives = [];

        try {
            $currentPage = 1;

            while ($url) {
                Log::info("Scraping URL (Page {$currentPage}): {$url}");
                $currentPage++;

                $response = $client->request('GET', $url);
                $html = $response->getBody()->getContents();
                $crawler = new Crawler($html);

                if ($crawler->filter('table.view_details')->count() === 0) {
                    Log::warning("Table 'view_details' not found on page.");
                    break;
                }

                $directives = $crawler->filter('table.view_details tr')->each(function (Crawler $node) use ($client, $search) {
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
                                $directory = 'presidential_directives';

                                Storage::disk('public')->put($directory . '/' . $pdfFilename, $pdfContent, 'public');
                            }
                        } catch (\Exception $e) {
                            Log::warning("Failed to fetch/download PDF for {$title}: " . $e->getMessage());
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

                $directives = array_filter($directives);

                foreach ($directives as $directive) {
                    if (!array_key_exists($directive['reference'], $uniqueDirectives)) {
                        $uniqueDirectives[$directive['reference']] = $directive;

                        $fileValue = $directive['file'] ?? null;

                        $record = PresidentialDirective::firstOrNew(['reference' => $directive['reference']]);

                        $record->title = $directive['title'];
                        $record->link = $directive['link'];
                        $record->date = $directive['date'];
                        $record->download_link = $directive['download_link'];
                        $record->file = $fileValue;

                        $record->save();

                        $saved = PresidentialDirective::where('reference', $directive['reference'])->first();
                        Log::info("Saved record file value:", ['file' => $saved->file]);


                    }
                }

                $nextPageNode = $crawler->filter('li.pWord a:contains("next")');
                if ($nextPageNode->count() > 0) {
                    $nextPageHref = $nextPageNode->attr('href');
                    $url = $nextPageHref ? (str_starts_with($nextPageHref, 'http') ? $nextPageHref : 'https://dilg.gov.ph' . $nextPageHref) : null;
                    if (!$url)
                        break;
                } else {
                    $url = null;
                    Log::info('No more pages to scrape.');
                }
            }

            return [
                'success' => true,
                'acts' => array_values($uniqueDirectives)
            ];
        } catch (\Exception $e) {
            Log::error('Error scraping data: ' . $e->getMessage());
            return ['error' => 'Error scraping data: ' . $e->getMessage()];
        }
    }

    public function sendPresidentialDirectivesToTangkaraw()
    {
        $presidentialDirectives = DB::connection('dilg')->table('presidential_directives')->get();

        if ($presidentialDirectives->isEmpty()) {
            Log::warning('No presidential directives found in the DILG database.');
            session()->flash('error', 'No presidential directives to send.');
            return;
        }

        Log::info('Fetched presidential directives from DILG database:', $presidentialDirectives->toArray());

        $presidentialDirectivesData = $presidentialDirectives->map(function ($presidentialDirective) {
            return [
                'title' => $presidentialDirective->title,
                'link' => $presidentialDirective->link,
                'reference' => $presidentialDirective->reference,
                'date' => $presidentialDirective->date,
                'download_link' => $presidentialDirective->download_link,
            ];
        })->toArray();

        Log::info('Prepared presidential directives to send:', $presidentialDirectivesData);

        if (empty($presidentialDirectivesData)) {
            Log::warning('Mapped presidential directives data is empty. Nothing to send.');
            session()->flash('error', 'No presidential directives to send.');
            return;
        }

        // Send to Tangkaraw
        Log::info('Sending presidential directives to Tangkaraw:', ['payload' => $presidentialDirectivesData]);

        $response = Http::post('http://127.0.0.1:8000/webhook/presidential-directive', [
            'presidential_directives' => $presidentialDirectivesData,
        ]);

        Log::info('Response from Tangkaraw:', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            session()->flash('message', 'All presidential directives sent successfully');
        } else {
            session()->flash('error', 'Failed to send presidential directives to Tangkaraw');
        }
    }
}
