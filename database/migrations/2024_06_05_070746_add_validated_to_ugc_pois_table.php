<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidatedToUgcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ugc_pois', function (Blueprint $table) {
            $table->enum('validated', ['valid', 'invalid', 'not_validated'])->default('not_validated');
            $table->enum('water_flow_rate_validated', ['valid', 'invalid', 'not_validated'])->default('not_validated');
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
            $table->dropColumn(['validated', 'water_flow_rate_validated']);
        });
    }
}
