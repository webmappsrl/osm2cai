<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumExpectedToRegionsAndProvinces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->integer('num_expected')->default(1);
        });

        \App\Models\Region::where('name', 'Abruzzo')->first()->update(['num_expected' => 449]);
        \App\Models\Region::where('name', 'Basilicata')->first()->update(['num_expected' => 26]);
        \App\Models\Region::where('name', 'Calabria')->first()->update(['num_expected' => 234]);
        \App\Models\Region::where('name', 'Campania')->first()->update(['num_expected' => 559]);
        \App\Models\Region::where('name', 'Emilia Romagna')->first()->update(['num_expected' => 1253]);
        \App\Models\Region::where('name', 'Friuli Venezia Giulia')->first()->update(['num_expected' => 645]);
        \App\Models\Region::where('name', 'Lazio')->first()->update(['num_expected' => 1033]);
        \App\Models\Region::where('name', 'Liguria')->first()->update(['num_expected' => 806]);
        \App\Models\Region::where('name', 'Lombardia')->first()->update(['num_expected' => 3784]);
        \App\Models\Region::where('name', 'Marche')->first()->update(['num_expected' => 602]);
        \App\Models\Region::where('name', 'Molise')->first()->update(['num_expected' => 27]);
        \App\Models\Region::where('name', 'Piemonte')->first()->update(['num_expected' => 4635]);
        \App\Models\Region::where('name', 'Puglia')->first()->update(['num_expected' => 60]);
        \App\Models\Region::where('name', 'Sardegna')->first()->update(['num_expected' => 292]);
        \App\Models\Region::where('name', 'Sicilia')->first()->update(['num_expected' => 330]);
        \App\Models\Region::where('name', 'Toscana')->first()->update(['num_expected' => 2610]);
        \App\Models\Region::where('name', 'Trentino Alto Adige')->first()->update(['num_expected' => 5162]);
        \App\Models\Region::where('name', 'Umbria')->first()->update(['num_expected' => 444]);
        \App\Models\Region::where('name', 'Valle d\'Aosta')->first()->update(['num_expected' => 1118]);
        \App\Models\Region::where('name', 'Veneto')->first()->update(['num_expected' => 984]);

        Schema::table('provinces', function (Blueprint $table) {
            $table->integer('num_expected')->default(1);
        });

        \Illuminate\Support\Facades\DB::statement('update provinces as p set num_expected=(SELECT num_expected from regions as r where r.id=p.region_id)/(SELECT count(*) from provinces as p1 where p1.region_id=p.region_id);');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('num_expected');
        });
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('num_expected');
        });
    }
}
