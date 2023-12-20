<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUgcPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ugc_pois', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('geohub_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->geometry('geometry', 4326)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->jsonb('raw_data')->nullable();
            $table->string('taxonomy_wheres')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ugc_pois');
    }
}
