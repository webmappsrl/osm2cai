<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToHikingRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hiking_routes', function (Blueprint $table) {
            $table->jsonb('natural_springs')->nullable();
            $table->boolean('has_natural_springs')->default(false);
            $table->jsonb('cai_huts')->nullable();
            $table->boolean('has_cai_huts')->default(false);
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
            $table->dropColumn('natural_springs');
            $table->dropColumn('has_natural_springs');
            $table->dropColumn('cai_huts');
            $table->dropColumn('has_cai_huts');
        });
    }
}
