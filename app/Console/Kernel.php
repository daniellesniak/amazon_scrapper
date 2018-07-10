<?php

namespace App\Console;

use App\Entities\Product;
use App\Entities\Product\Asin;
use App\Jobs\GetAsinNumbersFromSpecificCategory;
use App\Jobs\GetSingleProductData;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            if (Asin::count() > Product::count()) {
                dispatch(new GetSingleProductData);
            } else {
                dispatch(new GetAsinNumbersFromSpecificCategory);
            }
        })->everyMinute();

        $schedule->call(function () {
            file_put_contents(storage_path('logs/lumen.log'),'');
        })->daily();
    }
}
