<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionFavoritePublicationDateToHikingRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->date('region_favorite_publication_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->dropColumn('region_favorite_publication_date');
        });
    }
}
