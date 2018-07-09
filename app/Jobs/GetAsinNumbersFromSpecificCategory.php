<?php

namespace App\Jobs;

use App\Repositories\Category\LinkRepository;
use App\Repositories\Product\AsinRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class GetAsinNumbersFromSpecificCategory extends Job
{
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param LinkRepository $linkRepository
     * @param AsinRepository $asinRepository
     * @param Client $client
     * @return int
     */
    public function handle(LinkRepository $linkRepository, AsinRepository $asinRepository, Client $client): int
    {
        $link = $linkRepository->findWhere(['completed' => false])->first();
        $url = $this->generateUrl($link);

        dump('Page: ' . $link['current_page']);

        try {
            $subCategoryHtml = $client->get($url)->getBody()->getContents();
        } catch (ClientException $e) {
            Log::alert('GuzzleHttp throw an Exception', [
                'status_code' => $e->getCode(),
                'message' => $e->getMessage(),
                'class' => get_class($this),
                'line' => $e->getLine()
            ]);

            return false;
        }

        $crawler = new Crawler($subCategoryHtml);

        if ($this->checkIfNoResults($crawler)) {
            // if there are no results it means the page counter gone to far - check as completed
            $linkRepository->update([
                'completed' => true
            ], $link['id']);
        }

        $counter = 1;
        $crawler->filter('div.a-column.a-span12.a-text-center.s-position-relative > a')->each(function ($node) use ($asinRepository, &$counter) {
            preg_match('/\d{10}/', $node->attr('href'), $asin); // extract asin number from href

            $asin = reset($asin);
            dump('Product ASIN: ' . $asin);

            $asinRepository->firstOrCreate([
                'asin' => $asin
            ]);

            $counter++;
        });

        $linkRepository->incrementCurrentPage($link['id']);
        dump('Total items: ' . $counter);

        return true;
    }

    /**
     * @param $link
     * @return mixed|string
     */
    private function generateUrl($link)
    {
        $url = 'https://www.amazon.co.uk' . $link['url'];
        $url = str_replace('&page=1', '&page=' . $link['current_page'], $url);
        $url .= '&lo=stripbooks';

        return $url;
    }

    /**
     * No results mean the page counter gone too far.
     *
     * @param $crawler
     * @return bool
     */
    private function checkIfNoResults($crawler)
    {
        return $crawler->filter('#noResultsTitle')->count() > 0;
    }
}
