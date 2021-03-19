<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string("name", 254);
            $table->geometry("geometry");
            $table->string("code", 1);
            $table->string("full_code", 5);//->unique();
            $table->unsignedBigInteger("area_id");
            $table->timestamps();

            $table->foreign("area_id")
                ->references("id")
                ->on("areas");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sectors');
    }
}
