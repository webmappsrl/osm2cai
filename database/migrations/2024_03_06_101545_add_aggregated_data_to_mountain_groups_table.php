<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAggregatedDataToMountainGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mountain_groups', function (Blueprint $table) {
            $table->jsonb('aggregated_data')->nullable();
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
            $table->dropColumn('aggregated_data');
        });
    }
}
