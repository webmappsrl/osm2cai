<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidatorRolesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_archaeological_area_validator')->default(false);
            $table->boolean('is_signs_validator')->default(false);
            $table->boolean('is_geological_site_validator')->default(false);
            $table->boolean('is_archaeological_site_validator')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_archaeological_area_validator', 'is_signs_validator', 'is_geological_site_validator', 'is_archaeological_site_validator']);
        });
    }
}
