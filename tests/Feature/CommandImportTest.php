<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use App\Models\Sector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommandImportTest extends TestCase
{
    use RefreshDatabase;

    public function testFake() {
        $this->assertTrue(true);
    }

    private function _createSchema()
    {
        $schemaConnection = Schema::connection("pgsql_cai");
        $schemaConnection->dropIfExists("aree_settori");
        $schemaConnection->create("aree_settori", function (Blueprint $table) {
            $table->string("regione_codice_cai", 1)->nullable();
            $table->bigInteger("provincia_codice")->nullable();
            $table->string("area_codice", 10)->nullable();
            $table->string("settore_codice", 10)->nullable();
            $table->string("regione_codice", 5)->nullable();
            $table->string("regione_nome", 50)->nullable();
            $table->geometry("geom")->nullable();
            $table->bigInteger("id")->primary();
            $table->string("provincia_nome", 100)->nullable();
            $table->string("provincia_sigla", 5)->nullable();
        });
    }

    private function _insertFakeSectors()
    {
//        Regions: L, O
//        Provinces: PI, LI, RO
//        Areas: PIA, PIB, LIA, ROA
//        Sectors: PIA1, PIA2, PIB1, LIA1, ROA1
        $polygon = json_encode([
            "type" => "Polygon",
            "coordinates" => [[
                [0, 0],
                [0, 1],
                [1, 1],
                [1, 0],
                [0, 0]
            ]]
        ]);
        $connection = DB::connection('pgsql_cai');
        $table = $connection->table('aree_settori');
        $table->insert([
            "regione_codice_cai" => "L",
            "provincia_codice" => 1,
            "area_codice" => "A",
            "settore_codice" => "1",
            "regione_codice" => "L",
            "regione_nome" => "Toscana",
            "geom" => DB::raw('ST_GeomFromGeoJSON(\'' . $polygon . '\')'),
            "id" => 1,
            "provincia_nome" => "Pisa",
            "provincia_sigla" => "PI",
        ]);
        $table->insert([
            "regione_codice_cai" => "L",
            "provincia_codice" => 1,
            "area_codice" => "A",
            "settore_codice" => "2",
            "regione_codice" => "L",
            "regione_nome" => "Toscana",
            "geom" => DB::raw('public.ST_GeomFromGeoJSON(\'' . $polygon . '\')'),
            "id" => 2,
            "provincia_nome" => "Pisa",
            "provincia_sigla" => "PI",
        ]);
        $table->insert([
            "regione_codice_cai" => "L",
            "provincia_codice" => 1,
            "area_codice" => "B",
            "settore_codice" => "1",
            "regione_codice" => "L",
            "regione_nome" => "Toscana",
            "geom" => DB::raw('ST_GeomFromGeoJSON(\'' . $polygon . '\')'),
            "id" => 3,
            "provincia_nome" => "Pisa",
            "provincia_sigla" => "PI",
        ]);
        $table->insert([
            "regione_codice_cai" => "L",
            "provincia_codice" => 2,
            "area_codice" => "A",
            "settore_codice" => "1",
            "regione_codice" => "L",
            "regione_nome" => "Toscana",
            "geom" => DB::raw('ST_GeomFromGeoJSON(\'' . $polygon . '\')'),
            "id" => 4,
            "provincia_nome" => "Pisa",
            "provincia_sigla" => "LI",
        ]);
        $table->insert([
            "regione_codice_cai" => "O",
            "provincia_codice" => 3,
            "area_codice" => "A",
            "settore_codice" => "1",
            "regione_codice" => "O",
            "regione_nome" => "Lazio",
            "geom" => DB::raw('ST_GeomFromGeoJSON(\'' . $polygon . '\')'),
            "id" => 5,
            "provincia_nome" => "Roma",
            "provincia_sigla" => "RO",
        ]);

        $this->assertCount(5, $table->get());
    }

    public function _testImport()
    {
        $this->_createSchema();
        $this->_insertFakeSectors();

        $this->assertCount(0, Region::all());
        $this->assertCount(0, Province::all());
        $this->assertCount(0, Area::all());
        $this->assertCount(0, Sector::all());
        Artisan::call('osm2cai:import');

        $this->assertCount(2, Region::all());
        $this->assertCount(3, Province::all());
        $this->assertCount(4, Area::all());
        $this->assertCount(5, Sector::all());
    }
}
