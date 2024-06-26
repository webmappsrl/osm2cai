<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import sectors, areas, provinces and regions from the CAI postgis';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Log::info("Starting import from cai postgres");

        Log::info("Creating temporary tables...");
        $this->_createTempTables();
        Log::info("Temporary tables created successfully");

        Log::info("Connecting to cai DB...");
        $caiDb = DB::connection("pgsql_cai");
        Log::info("Connection successful");
        Log::info("Retrieving all sectors from aree_settori...");
        $sectors = $caiDb->table("aree_settori")->get("*");
        Log::info(count($sectors) . " sectors found");

        $osm2caiDb = DB::connection("osm2cai");
        try {
            $osm2caiDb->table("temp_sectors")->insert(json_decode(json_encode($sectors), true));
        } catch (\Throwable $e) {
            Log::error("Import failed during osm2cai database insertions. Aborting");
            return 1;
        }

        Log::info("Connecting to osm2cai DB...");
        $osm2caiDb = DB::connection("osm2cai");
        Log::info("Connection successful");

        Log::info("Truncating existing table...");
        $osm2caiDb->table("sectors")->truncate();
        $osm2caiDb->table("areas")->truncate();
        $osm2caiDb->table("provinces")->truncate();
        $osm2caiDb->table("regions")->truncate();
        Log::info("Tables truncated successfully");

        $this->_importRegions($osm2caiDb);
        $this->_importProvinces($osm2caiDb);
        $this->_importAreas($osm2caiDb);
        $this->_importSectors($osm2caiDb);

        Log::info("Dropping temporary tables...");
        $this->_dropTempTables();
        Log::info("Temporary tables dropped successfully");

        Log::info("Import completed successfully");
        return 0;
    }

    private function _createTempTables()
    {
        $schemaConnection = Schema::connection("osm2cai");
        Log::info("Creating temp_sectors table...");
        $schemaConnection->dropIfExists("temp_sectors");
        $schemaConnection->create("temp_sectors", function (Blueprint $table) {
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
        Log::info("Table temp_sectors created successfully");
    }

    private function _dropTempTables()
    {
        $schemaConnection = Schema::connection("osm2cai");
        Log::info("Dropping temp_sectors table...");
        $schemaConnection->dropIfExists("temp_sectors");
        Log::info("Table temp_sectors dropped successfully");
    }

    private function _importRegions(ConnectionInterface $db)
    {
        Log::info("Importing regions...");
        $db->beginTransaction();
        Log::info("Setting standard region names...");
        foreach (config('geometry_mapping.regions') as $key => $name) {
            $db->select(
                "UPDATE temp_sectors
                    SET regione_nome = ?
                    WHERE regione_codice_cai = ?;", [$name, $key]);
        }
        Log::info("Standard region names set");
        $db->select(
            "INSERT INTO regions(
                        name,
                        geometry,
                        code
                    )
                    SELECT
                           regione_nome,
                           ST_UNION(geom),
                           regione_codice_cai
                        FROM temp_sectors
                        GROUP BY regione_nome, regione_codice_cai;");
        $db->commit();
        Log::info("Regions imported successfully");
    }

    private function _importProvinces($db)
    {
        Log::info("Importing provinces...");
        $db->beginTransaction();
        Log::info("Setting standard province names...");
        foreach (config('geometry_mapping.provinces') as $key => $name) {
            $db->select(
                "UPDATE temp_sectors
                    SET provincia_nome = ?
                    WHERE provincia_sigla = ?;", [$name, $key]);
        }
        Log::info("Standard province names set");
        $db->select(
            "INSERT INTO provinces(
                        name,
                        geometry,
                        full_code,
                        code,
                        region_id
                    )
                    SELECT
                           provincia_nome,
                           ST_UNION(geom),
                           regions.code || provincia_sigla,
                           provincia_sigla,
                           regions.id
                        FROM
                            temp_sectors INNER JOIN regions ON (temp_sectors.regione_codice_cai = regions.code)
                        GROUP BY provincia_sigla, regions.id, provincia_nome;");
        $db->commit();
        Log::info("Provinces imported successfully");
    }

    private function _importAreas($db)
    {
        Log::info("Importing areas...");
        $db->beginTransaction();
        $db->select(
            "INSERT INTO areas(
                        name,
                        geometry,
                        full_code,
                        code,
                        province_id
                    )
                    SELECT
                           provinces.full_code || area_codice,
                           ST_UNION(geom),
                           provinces.full_code || area_codice,
                           area_codice,
                           provinces.id
                        FROM
                            temp_sectors INNER JOIN provinces ON (temp_sectors.provincia_sigla = provinces.code)
                        GROUP BY area_codice, provinces.code, provinces.id;");
        $db->commit();
        Log::info("Areas imported successfully");
    }

    private function _importSectors($db)
    {
        Log::info("Importing sectors...");
        $db->beginTransaction();
        Log::info("Overwriting long codes (more than one char)...");
        $db->select(
            "UPDATE temp_sectors
                    SET settore_codice = '-' WHERE length(settore_codice) > 1;");
        Log::info("Long code removed");
        $db->select(
            "INSERT INTO sectors(
                        name,
                        geometry,
                        full_code,
                        code,
                        area_id
                    )
                    SELECT
                           areas.full_code || settore_codice,
                           geom,
                           areas.full_code || settore_codice,
                           settore_codice,
                           areas.id
                        FROM
                            temp_sectors INNER JOIN areas ON (temp_sectors.regione_codice_cai || temp_sectors.provincia_sigla || temp_sectors.area_codice = areas.full_code);");
        $db->commit();
        Log::info("Sectors imported successfully");
    }
}
