<?php

namespace App\Jobs;

use App\Repositories\Product\AsinRepository;
use App\Repositories\ProductRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class DownloadSingleProductJob extends Job
{
    protected $client;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Execute the job.
     *
     * @param ProductRepository $productRepository
     * @param AsinRepository $asinRepository
     * @return void
     */
    public function handle(ProductRepository $productRepository, AsinRepository $asinRepository)
    {
        $asinNumbers = $asinRepository->findWhere(['is_crawled' => false])->take(100);

        $client = $this->client;

        foreach ($asinNumbers as $asin) {
            dump($asin['asin']);
            $html = $client->get('https://www.amazon.co.uk/dp/' . $asin['asin'])->getBody()->getContents();
            $crawler = new Crawler($html);

            if ($crawler->filter('div#dp-container')->count() == 0) {
                Log::notice('The page is not a valid product page!', [
                    'asin' => $asin['asin'],
                    'full_url' => 'https://www.amazon.co.uk/dp/' . $asin['asin'],
                    'class' => get_class($this),
                    'line' => __LINE__
                ]);

                $asinRepository->update(['is_crawled' => true], $asin['id']);
                continue;
            }

            $title = $this->getTitle($crawler);
            dump($title);
            $description = $this->getDescription($html);
            $price = $this->getPrice($crawler);
            dump($price);

            $product = $productRepository->firstOrCreate([
                'title' => $title,
                'description' => $description,
                'price' => (float)$price
            ]);

            if ($imageUrl = $crawler->filter('div.imageThumb.thumb > img')->count() > 0) {
                $imageUrl = $crawler->filter('div.imageThumb.thumb > img')->attr('src');
                $imageUrl = str_replace('._AC_SX60_CR,0,0,60,60_', '', $imageUrl);

                $this->downloadImage(storage_path('app/'. $product->id .'.jpg'), $imageUrl);
            }

            $asinRepository->update(['is_crawled => true'], $asin['id']);
        }
    }

    /**
     * @param $path
     * @param $imageUrl
     * @return bool
     */
    private function downloadImage($path, $imageUrl): bool
    {
        $resource = fopen($path, 'w');
        $this->client->get($imageUrl, ['sink' => $resource]);

        return exif_imagetype($path);
    }

    /**
     * @param $crawler
     * @return string
     */
    private function getTitle($crawler): string
    {
        if ($crawler->filter('span#productTitle')->count() === 0) {
            $title = '[no title]';
        }
        else {
            $title = str_before($crawler->filter('title')->text(), ':');
        }

        return $title;
    }

    /**
     * @param $crawler
     * @return string
     */
    private function getPrice($crawler): string
    {
        if ($crawler->filter('span.a-size-medium.a-color-price.offer-price.a-text-normal')->count() === 0) {
            $price = 0;
        } else {
            $price = $crawler->filter('span.a-size-medium.a-color-price.offer-price.a-text-normal')->first()->text();
            $price = substr($price, 2);
        }
        return $price;
    }

    /**
     * Crawler can't see these divs so we need to pull out description in this ugly way.
     *
     * @param $html
     * @return string
     */
    public function getDescription($html): string
    {
        $html = str_after($html, '<div id="bookDescription_feature_div" class="feature" data-feature-name="bookDescription">');
        $html = str_before($html, '<div id="outer_postBodyPS" style="overflow: hidden; z-index: 1; height: 0px; display: block;">');
        $html = str_after($html, '<noscript>');
        $html = str_replace('</noscript>', '', $html);

        return $html;
    }
}