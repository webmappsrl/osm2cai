<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLatLngAndAddGeometryToEcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ec_pois', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lng');
            $table->geometry('geometry')->nullable();
            $table->jsonb('tags')->nullable();
            $table->enum('type', ['signed_post', 'huts', 'water_spring', 'generic'])->default('generic');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ec_pois', function (Blueprint $table) {
            $table->dropColumn('geometry');
            $table->dropColumn('tags');
            $table->dropColumn('type');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
        });
    }
}
