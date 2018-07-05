<?php

namespace App\Jobs;

use App\Repositories\Product\AsinRepository;
use Goutte\Client;
use Illuminate\Support\Facades\Log;
use Prettus\Validator\Exceptions\ValidatorException;

class GetAsinNumbersFromSpecificCategory extends Job
{
    protected $url;
    protected $client;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        // set Books Category URL
        $this->url = 'https://www.amazon.co.uk/s/ref=sr_pg_1?rh=n%3A266239%2Cp_n_shipping_option-bin%3A2023186031%2Cp_n_availability%3A428631031&bbn=266239&ie=UTF8&qid=1530721506&lo=stripbooks';
        $this->client = new Client();
    }

    /**
     * Execute the job.
     *
     * @param AsinRepository $linkRepository
     * @return int
     */
    public function handle(AsinRepository $linkRepository): int
    {
        $startUrl = $this->url;
        $client = $this->client;

        $pages = $this->checkPageFile();

        dump('Page: ' . $pages['current'] . '/' . $pages['last']);

        $startUrl .= '&page=' . $pages['current'];
        $crawler = $client->request('GET', $startUrl);

        $counter = 1;
        $crawler->filter('div.a-column.a-span12.a-text-center.s-position-relative > a')->each(function ($node) use ($linkRepository, &$counter) {
            preg_match('/\d{10}/', $node->attr('href'), $asin);

            $asin = reset($asin);
            dump('Product ASIN: ' . $asin);

            $linkRepository->firstOrCreate([
                'asin' => !empty($asin) ? $asin : null
            ]);

            $counter += 1;
        });

        dump('Total: ' . $counter);

        $this->incrementPageFile();

        return true;
    }

    /**
     * @param $crawler
     * @return array
     */
    private function checkPageFile(): array
    {
        if (!file_exists(storage_path('page.txt'))) {
            $crawler = $this->client->request('GET', $this->url);
            $allPages = $crawler->filter('.pagnDisabled')->first()->text();
            $pageFile = fopen(storage_path('page.txt'), 'w');
            fwrite($pageFile, '1/' . $allPages);
            fclose($pageFile);

            $this->checkPageFile();
        }

        $pageFile = fopen(storage_path('page.txt'), 'r');
        $pages = fread($pageFile, filesize(storage_path('page.txt')));
        fclose($pageFile);

        $pages = explode('/', $pages);

        return [
            'current' => $pages[0],
            'last' => $pages[1]
        ];
    }

    private function incrementPageFile()
    {
        $file = fopen(storage_path('page.txt'), 'r');
        $pages = fread($file, filesize(storage_path('page.txt')));
        ftruncate($file, filesize(storage_path('page.txt')));
        fclose($file);

        $pages = trim($pages);
        $pages = explode('/', $pages);

        $currentPage = $pages[0] + 1;
        $lastPage = $pages[1];

        $file = fopen(storage_path('page.txt'), 'w');
        fwrite($file, $currentPage . '/' . $lastPage);
        fclose($file);

        return true;
    }
}
