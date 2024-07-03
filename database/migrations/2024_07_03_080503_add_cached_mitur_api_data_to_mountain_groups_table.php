<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCachedMiturApiDataToMountainGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->json('cached_mitur_api_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->dropColumn('cached_mitur_api_data');
        });
    }
}
