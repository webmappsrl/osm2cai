<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumExpectedToRegionsAndProvinces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->integer('num_expected')->default(1);
        });
        
        Schema::table('provinces', function (Blueprint $table) {
            $table->integer('num_expected')->default(1);
        });

        \Illuminate\Support\Facades\DB::statement('update provinces as p set num_expected=(SELECT num_expected from regions as r where r.id=p.region_id)/(SELECT count(*) from provinces as p1 where p1.region_id=p.region_id);');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('num_expected');
        });
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('num_expected');
        });
    }
}
