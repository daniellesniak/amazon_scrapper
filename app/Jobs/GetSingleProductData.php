<?php

namespace App\Jobs;

use App\Helpers\ProxyManager;
use App\Repositories\Product\AsinRepository;
use App\Repositories\ProductRepository;
use Campo\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class GetSingleProductData extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param ProductRepository $productRepository
     * @param AsinRepository $asinRepository
     * @return bool
     */
    public function handle(ProductRepository $productRepository, AsinRepository $asinRepository)
    {
        $proxyManager = new ProxyManager(storage_path('proxy_list.txt'));
        $proxies = $proxyManager->getValidatedProxies();

        for ($i = 0; $i < count($proxies); $i++) {
            $client = new Client();
            $asinNumbers = $asinRepository->findWhere(['is_crawled' => false])->take(20);

            foreach ($asinNumbers as $asin) {
                if ($asin['asin'] == 0) {
                    $asinRepository->update(['is_crawled' => true], $asin->id);
                    continue;
                }

                dump($asin['asin']);

                try {
                    $randomUserAgent = UserAgent::random();
                } catch (\Exception $e) {
                    $randomUserAgent = 'Mozilla/5.0 (Windows NT 6.2; rv:20.0) Gecko/20121202 Firefox/20.0';
                }

                try {
                    $html = $client->get('https://www.amazon.co.uk/dp/' . $asin['asin'], [
                        'proxy' => $proxies[$i],
                        'headers' => [
                            'User-Agent' => $randomUserAgent,
                        ]
                    ])->getBody()->getContents();
                } catch (ClientException | ServerException $e) {
                    Log::notice('GuzzleHttp throw an ' . get_class($e), [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'class' => get_class($this),
                        'line' => __LINE__
                    ]);

                    continue;
                }

                $crawler = new Crawler($html);

                if ($crawler->filter('.a-container > .a-row.a-spacing-double-large .a-box > .a-box-inner > h4')->count() > 0) {
                    Log::notice('Amazon Robot Check (Captcha)', [
                        'class' => get_class($this),
                        'line' => __LINE__
                    ]);

                    return false;
                }

                if ($crawler->filter('div#dp-container')->count() == 0) {
                    Log::notice('The page is not a valid product page!', [
                        'asin' => $asin['asin'],
                        'full_url' => 'https://www.amazon.co.uk/dp/' . $asin['asin'],
                        'class' => get_class($this),
                        'line' => __LINE__
                    ]);

                    continue;
                }

                $title = $this->getTitle($crawler);
                dump($title);

                $description = $this->getDescription($html);
                $price = $this->getPrice($crawler);
                dump($price);
                $additionalInfo = $this->getAdditionalInfo($html);

                $product = $productRepository->firstOrCreate([
                    'title' => $title,
                    'description' => $description,
                    'price' => (float)$price,
                    'details' => $additionalInfo != false ? $additionalInfo : null
                ]);

                if ($imageUrl = $crawler->filter('div.imageThumb.thumb > img')->count() > 0) {
                    $imageUrl = $crawler->filter('div.imageThumb.thumb > img')->attr('src');
                    $imageUrl = str_replace('._AC_SX60_CR,0,0,60,60_', '', $imageUrl);

                    $this->downloadImage(storage_path('app/' . $product->id . '.jpg'), $imageUrl);
                }

                $asinRepository->update(['is_crawled' => true], $asin['id']);
            }
        }

        return true;
    }

    /**
     * @param $path
     * @param $imageUrl
     * @return bool
     */
    private function downloadImage($path, $imageUrl): bool
    {
        $client = new Client();
        $resource = fopen($path, 'w');
        $client->get($imageUrl, ['sink' => $resource]);

        return exif_imagetype($path);
    }

    /**
     * @param $crawler
     * @return string
     */
    private function getTitle($crawler): string
    {
        if ($crawler->filter('title')->count() === 0) {
            $title = '[no title]';
        } else {
            $title = str_before($crawler->filter('title')->text(), ': Amazon');
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
     * @return string|null
     */
    public function getDescription($html): ?string
    {
        $html = str_after($html, '<div id="bookDescription_feature_div" class="feature" data-feature-name="bookDescription">');
        $html = str_before($html, '<div id="outer_postBodyPS" style="overflow: hidden; z-index: 1; height: 0px; display: block;">');
        $html = str_after($html, '<noscript>');
        $html = str_replace('</noscript>', '', $html);

        if (strlen($html) > 20000) {
            // if > 20000 it means there is no description (and all the html is returned)
            $html = null;
        }

        return $html;
    }

    /**
     * @param $html
     * @return array|bool
     */
    private function getAdditionalInfo($html)
    {
        $html = str_after($html, '<div id="detail_bullets_id">');
        $html = str_before($html, '</div>');

        $crawler = new Crawler($html);
        if ($crawler->filter('.bucket > .content > ul > li')->count() === 0) {
            return false;
        }

        $matches = [];
        $crawler->filter('.bucket > .content > ul > li')->each(function ($node) use (&$matches) {
            $matches[] = explode(':', $node->text());
        });

        $additionalInfo = [];
        foreach ($matches as $match) {
            // indexes: [0] - key eg. Publisher, [1] - value eg. Cambridge University Press
            if (count($match) > 2 || !isset($match[1]) || str_contains($match[1], PHP_EOL)) {
                continue;
            }

            $additionalInfo[str_slug($match[0], '_')] = ltrim($match[1]);
        }

        if (count($additionalInfo) === 0) {
            return false;
        }

        return json_encode($additionalInfo);
    }
}