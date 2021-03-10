<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string("name", 254);
            $table->geometry("geometry");
            $table->string("code", 2)->unique();
            $table->string("full_code", 3)->unique();
            $table->unsignedBigInteger("region_id");
            $table->timestamps();

            $table->foreign("region_id")
                ->references("id")
                ->on("regions");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provinces');
    }
}
