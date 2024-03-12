<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCascadeOnDeleteMountainGroupsRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mountain_groups_region', function (Blueprint $table) {
            $table->dropForeign(['mountain_group_id']);
            $table->dropForeign(['region_id']);
            $table->foreign('mountain_group_id')->references('id')->on('mountain_groups')->onDelete('cascade');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['mountain_group_id']);
        $table->dropForeign(['region_id']);
        $table->foreign('mountain_group_id')->references('id')->on('mountain_groups');
        $table->foreign('region_id')->references('id')->on('regions');
    }
}
