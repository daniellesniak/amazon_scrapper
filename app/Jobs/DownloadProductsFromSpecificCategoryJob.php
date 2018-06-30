<?php

namespace App\Jobs;

use App\Repositories\Product\LinkRepository;
use Goutte\Client;
use Illuminate\Support\Facades\Log;
use Prettus\Validator\Exceptions\ValidatorException;

class DownloadProductsFromSpecificCategory extends Job
{
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param $categoryUrl
     */
    public function __construct($categoryUrl)
    {
        $this->url = $categoryUrl;
    }

    /**
     * Execute the job.
     *
     * @param LinkRepository $linkRepository
     * @return int
     */
    public function handle(LinkRepository $linkRepository): int
    {
        $client = new Client();

        $crawler = $client->request('GET', $this->url);


        $errors = [];
        $crawler->filter('div.a-column.a-span12.a-text-center.s-position-relative > a')->each(function ($node) use ($linkRepository) {
            try {
                $linkRepository->firstOrCreate([
                    'url' => !empty($node->attr('href')) ? $node->attr('href') : null
                ]);
            } catch (ValidatorException $e) {
                Log::notice('URL is not valid!', [
                    'url' => $this->url,
                    'class' => get_class($this),
                    'line' => __LINE__
                ]);

                $errors['VALIDATION_ERROR'] = 'The URL is not valid.';
            }
        });

        if (($errors instanceof \Countable) && count($errors) > 0) {
            var_dump($errors); // todo to todo to do todo to do todo
            return false;
        }

        return true;
    }
}
