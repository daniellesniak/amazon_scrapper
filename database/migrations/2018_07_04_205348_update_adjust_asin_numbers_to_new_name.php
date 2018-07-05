<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateAdjustAsinNumbersToNewName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asin_numbers', function (Blueprint $table) {
            $table->renameColumn('url', 'asin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asin_numbers', function (Blueprint $table) {
            $table->renameColumn('asin', 'url');
        });
    }
}
