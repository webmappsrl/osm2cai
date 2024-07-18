<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOsmfeaturesDataAndCachedMiturApiToCaiHutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cai_huts', function (Blueprint $table) {
            $table->json('osmfeatures_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cai_huts', function (Blueprint $table) {
            $table->dropColumn(['osmfeatures_data']);
        });
    }
}