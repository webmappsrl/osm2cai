<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->string('addr_city')->nullable();
            $table->string('addr_street')->nullable();
            $table->string('addr_housenumber')->nullable();
            $table->string('addr_postcode')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('opening_hours')->nullable();
            $table->string('wheelchair')->nullable();
            $table->string('fax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema_:table('sections', function (Blueprint $table) {
            $table->dropColumn('addr_city');
            $table->dropColumn('addr_street');
            $table->dropColumn('addr_housenumber');
            $table->dropColumn('addr_postcode');
            $table->dropColumn('website');
            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->dropColumn('opening_hours');
            $table->dropColumn('wheelchair');
            $table->dropColumn('fax');
        });
    }
}