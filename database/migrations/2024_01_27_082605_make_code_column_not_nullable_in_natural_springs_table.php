<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCodeColumnNotNullableInNaturalSpringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('natural_springs', function (Blueprint $table) {

            $table->string('code')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('natural_springs', function (Blueprint $table) {

            $table->string('code')->nullable(false)->change();
        });
    }
}
