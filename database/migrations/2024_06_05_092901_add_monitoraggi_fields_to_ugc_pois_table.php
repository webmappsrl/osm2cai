<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonitoraggiFieldsToUgcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->string('flow_rate_volume')->nullable();
            $table->string('flow_rate_fill_time')->nullable();
            $table->string('conductivity')->nullable();
            $table->string('temperature')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->dropColumn(['flow_rate_volume', 'flow_rate_fill_time', 'conductivity', 'temperature']);
        });
    }
}
