<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHrValidatorColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ugc_track_validator')->default(false);
        });

        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->enum('validated', ['valid', 'invalid', 'not_validated'])->default('not_validated');
            $table->unsignedBigInteger('validator_id')->nullable();
            $table->timestamp('validation_date')->nullable();
            $table->foreign('validator_id')->references('id')->on('users');
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
            $table->dropColumn('hr_validator');
        });

        Schema::table('ugc_tracks', function (Blueprint $table) {
            $table->dropColumn(['validated', 'validator_id', 'validation_date']);
        });
    }
}
