<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaiHutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cai_huts'))
            Schema::create('cai_huts', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->unsignedBigInteger('unico_id')->nullable();
                $table->string('name');
                $table->string('second_name')->nullable();
                $table->text('description')->nullable();
                $table->integer('elevation')->nullable();
                $table->string('owner')->nullable();
                $table->point('geometry');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cai_huts');
    }
}
