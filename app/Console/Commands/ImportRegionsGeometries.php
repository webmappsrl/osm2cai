<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpParser\ErrorHandler\Throwing;
use Throwable;

class import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:import_region_geometries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import region geometries from geojson got from ISTAT shp conversion';

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
        $this->info("Starting import ISTAT regions geometries ...");

        $configIstat = config('geometry_mapping.regions_istat');
        $geojson = json_decode(file_get_contents(__DIR__ . '/ISTAT-REGIONS-DETAILED.geojson'));
        $features = $geojson->features;



        try {
            DB::beginTransaction();
            foreach ($features as $regionObj) {

                $this->info( "Doing {$regionObj->properties->DEN_REG} ..." );

                $properties = $regionObj->properties;
                $regionGeojson = json_encode($regionObj->geometry);
                $istatCode = $properties->COD_REG;

                $osmRegionCode = $configIstat[$istatCode];

                DB::update(
                    "UPDATE regions SET geometry = ST_GeomFromGeoJSON(?) WHERE code = ?;",
                    [$regionGeojson, $osmRegionCode]
                );
            }

            DB::commit();
            $this->info("Import completed successfully");

        } catch (Throwable $t) {
            DB::rollBack();
            throw $t;
        }





        return 0;
    }
}
