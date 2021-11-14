<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PercentageNullToHikingRouteSector extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $st = \Illuminate\Support\Facades\DB::raw('ALTER TABLE hiking_route_sector ALTER COLUMN percentage DROP NOT NULL;');
        \Illuminate\Support\Facades\DB::statement($st);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hiking_route_sector', function (Blueprint $table) {
            //
        });
    }
}
