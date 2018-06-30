<?php

namespace App\Jobs;

use Goutte\Client;

class ScrapProductsFromCategoryPageJob extends Job
{
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        $crawler = $client->request('GET', $this->url);

        $crawler->filter('div.a-column.a-span12.a-text-center.s-position-relative > a')->each(function ($node) {
            //
        });
    }
}
