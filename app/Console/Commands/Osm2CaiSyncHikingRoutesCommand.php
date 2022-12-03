<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use App\Models\HikingRoutes;
use App\Models\HikingRoutesOsm;
use Illuminate\Console\Command;
use App\Services\GeometryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Providers\Osm2CaiHikingRoutesServiceProvider;

class Osm2CaiSyncHikingRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to sync osm part of the validation hiking routes table.';

    private $provider;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Retrieve routes from hiking_routes_osm table
        $routes = HikingRoutesOsm::get();
        $counter = 0; $tot = $routes->count();
        foreach($routes as $route) {
            $counter++;
            Log::info("$counter/$tot https://openstreetmap.org/relation/{$route->relation_id}");
            $this->sync($route,$counter,$tot);
        }
    }

    public function sync($route_osm,$counter,$tot)
    {
        $this->info('');
        $this->info("$counter/$tot https://openstreetmap.org/relation/{$route_osm->relation_id}");
        // Convert Object to array
        // $route_osm_array = (array) $route_osm;
        // Map keys
        // $route_cai_array = [];
        // foreach ($route_osm_array as $k => $v) {
        //     if ($k == 'relation_id') {
        //         ;
        //     } else if ($k == 'tags') {
        //         // TODO: json data convert
        //         // $route_cai_array['tags_osm']=$v;
        //     } else if ($k == 'geom') {
        //         $route_cai_array['geometry_osm'] = $v;
        //     } else {
        //         $route_cai_array[$k . '_osm'] = $v;
        //     }
        // }

        /**
         * @var \App\Models\HikingRoute
         */
        $route_cai = HikingRoute::firstOrCreate(['relation_id' => $route_osm->relation_id]);



        $this->info("Route has status {$route_cai->osm2cai_status }: SYNC");

        // Set fields to compute status
        $route_cai->cai_scale_osm = $route_osm->cai_scale;
        $route_cai->source_osm = $route_osm->source;



        // FILL OSM FIELDS
        foreach ([
            'ref', 'old_ref', 'source_ref', 'survey_date', 'name', 'rwn_name', 'ref_REI',
            'from', 'to', 'osmc_symbol', 'network', 'roundtrip', 'symbol', 'symbol_it',
            'ascent', 'descent', 'distance', 'duration_forward', 'duration_backward',
            'operator', 'state', 'description', 'description_it', 'website', 'wikimedia_commons',
            'maintenance', 'maintenance_it', 'note', 'note_it', 'note_project_page'
        ] as $k) {
            $k_osm=$k.'_osm';
            $route_cai->$k_osm=$route_osm->$k;
        }


        $service = app()->make(GeometryService::class);
        //force srid 4326
        $route_cai->geometry_osm = $service->geometryTo4326Srid($route_osm->geom);



        if($route_cai->osm2cai_status == 4 ) {
            $route_cai->save();
            $this->info("Route has status {$route_cai->osm2cai_status }: SYNC only osm field");
            return;
        }


        $route_cai->setOsm2CaiStatus();
        $route_cai->save();
        $this->info("Status set to:{$route_cai->osm2cai_status} cai_scale:{$route_cai->cai_scale_osm} source:{$route_cai->source_osm}");

        $route_cai->copyFromOsm2Cai();
        $route_cai->save();
        $route_cai->computeAndSetTechInfo();
        $route_cai->computeAndSetTerritorialUnits();
        $route_cai->save();

        return;
    }


}
