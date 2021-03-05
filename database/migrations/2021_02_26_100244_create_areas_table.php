<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->geometry("geometry");
            $table->string("code", 1);
            $table->string("full_code", 4);
            $table->unsignedBigInteger("province_id");
            $table->timestamps();

            $table->foreign("province_id")
                ->references("id")
                ->on("provinces");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
}
