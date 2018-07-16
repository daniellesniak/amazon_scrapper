<?php

namespace App\Jobs;

use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Log;

class SaveImagesAsIsbn extends Job
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
     * @param ProductRepository $productRepository
     * @return int
     */
    public function handle(ProductRepository $productRepository): int
    {
        $products = $productRepository->all(['id', 'details']);

        foreach ($products as $product) {
            $isbn = json_decode($product->details, true)['isbn_10'];
            $filePath = storage_path('app/') . $product->id . '.jpg';

            if (!file_exists($filePath)) {
                continue;
            }

            if (!copy($filePath, storage_path('isbn/') . $isbn . '.jpg')) {
                Log::alert('Can\'t copy image!', [
                    'file_source' => $filePath,
                    'file_destination' => storage_path('isbn/') . $isbn . '.jpg',
                    'class' => get_class($this),
                    'line' => __LINE__
                ]);
            }
        }

        return true;
    }
}
