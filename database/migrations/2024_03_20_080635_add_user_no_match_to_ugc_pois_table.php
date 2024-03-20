<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserNoMatchToUgcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->string('user_no_match')->nullable();
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
            $table->dropColumn('user_no_match');
        });
    }
}
