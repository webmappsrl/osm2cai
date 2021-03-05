<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

define("REGIONS", [
    "E" => "Piemonte",
    "F" => "Valle d'Aosta",
    "D" => "Lombardia",
    "C" => "Trentino Alto Adige",
    "B" => "Veneto",
    "A" => "Friuli Venezia Giulia",
    "G" => "Liguria",
    "H" => "Emilia Romagna",
    "L" => "Toscana",
    "N" => "Umbria",
    "M" => "Marche",
    "O" => "Lazio",
    "P" => "Abruzzo",
    "Q" => "Molise",
    "S" => "Campania",
    "R" => "Puglia",
    "T" => "Basilicata",
    "U" => "Calabria",
    "V" => "Sicilia",
    "Z" => "Sardegna"
]);
define("PROVINCES", [
    "AG" => "Agrigento",
    "AL" => "Alessandria",
    "AN" => "Ancona",
    "AO" => "Valle d'Aosta/Vallée d'Aoste",
    "AP" => "Ascoli Piceno",
    "AQ" => "L'Aquila",
    "AR" => "Arezzo",
    "AT" => "Asti",
    "AV" => "Avellino",
    "BA" => "Bari",
    "BG" => "Bergamo",
    "BI" => "Biella",
    "BL" => "Belluno",
    "BN" => "Benevento",
    "BO" => "Bologna",
    "BR" => "Brindisi",
    "BS" => "Brescia",
    "BT" => "Barletta-Andria-Trani",
    "BZ" => "Bolzano/Bozen",
    "CA" => "Cagliari",
    "CB" => "Campobasso",
    "CE" => "Caserta",
    "CH" => "Chieti",
    "CI" => "Carbonia-Iglesias",
    "CL" => "Caltanissetta",
    "CN" => "Cuneo",
    "CO" => "Como",
    "CR" => "Cremona",
    "CS" => "Cosenza",
    "CT" => "Catania",
    "CZ" => "Catanzaro",
    "EN" => "Enna",
    "FC" => "Forlì-Cesena",
    "FE" => "Ferrara",
    "FG" => "Foggia",
    "FI" => "Firenze",
    "FM" => "Fermo",
    "FR" => "Frosinone",
    "GE" => "Genova",
    "GO" => "Gorizia",
    "GR" => "Grosseto",
    "IM" => "Imperia",
    "IS" => "Isernia",
    "KR" => "Crotone",
    "LC" => "Lecco",
    "LE" => "Lecce",
    "LI" => "Livorno",
    "LO" => "Lodi",
    "LT" => "Latina",
    "LU" => "Lucca",
    "MB" => "Monza e della Brianza",
    "MC" => "Macerata",
    "ME" => "Messina",
    "MI" => "Milano",
    "MN" => "Mantova",
    "MO" => "Modena",
    "MS" => "Massa-Carrara",
    "MT" => "Matera",
    "NA" => "Napoli",
    "NO" => "Novara",
    "NU" => "Nuoro",
    "OG" => "Ogliastra",
    "OR" => "Oristano",
    "OT" => "Olbia-Tempio",
    "PA" => "Palermo",
    "PC" => "Piacenza",
    "PD" => "Padova",
    "PE" => "Pescara",
    "PG" => "Perugia",
    "PI" => "Pisa",
    "PN" => "Pordenone",
    "PO" => "Prato",
    "PR" => "Parma",
    "PT" => "Pistoia",
    "PU" => "Pesaro e Urbino",
    "PV" => "Pavia",
    "PZ" => "Potenza",
    "RA" => "Ravenna",
    "RC" => "Reggio Calabria",
    "RE" => "Reggio nell'Emilia",
    "RG" => "Ragusa",
    "RI" => "Rieti",
    "RM" => "Roma",
    "RN" => "Rimini",
    "RO" => "Rovigo",
    "SA" => "Salerno",
    "SI" => "Siena",
    "SO" => "Sondrio",
    "SP" => "La Spezia",
    "SR" => "Siracusa",
    "SS" => "Sassari",
    "SU" => "Sud Sardegna",
    "SV" => "Savona",
    "TA" => "Taranto",
    "TE" => "Teramo",
    "TN" => "Trento",
    "TO" => "Torino",
    "TP" => "Trapani",
    "TR" => "Terni",
    "TS" => "Trieste",
    "TV" => "Treviso",
    "UD" => "Udine",
    "VA" => "Varese",
    "VB" => "Verbano-Cusio-Ossola",
    "VC" => "Vercelli",
    "VE" => "Venezia",
    "VI" => "Vicenza",
    "VR" => "Verona",
    "VS" => "Medio Campidano",
    "VT" => "Viterbo",
    "VV" => "Vibo Valentia"
]);

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
        foreach (REGIONS as $key => $name) {
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
        foreach (PROVINCES as $key => $name) {
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
                            temp_sectors INNER JOIN areas ON (temp_sectors.area_codice = areas.code);");
        $db->commit();
        Log::info("Sectors imported successfully");
    }
}
