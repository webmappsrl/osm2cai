<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use App\Models\HikingRoutes;
use App\Providers\Osm2CaiHikingRoutesServiceProvider;
use Illuminate\Console\Command;

class Osm2CaiSyncHikingRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:sync_hiking_routes 
                            {code : Zone REI code, set to "italy" to import and sync all italy} 
                            {--dry-mode : Do not run sync, show only what is going on.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to sync osm part of the validation hiking routes table.';

    private $provider;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Osm2CaiHikingRoutesServiceProvider $provider)
    {
        parent::__construct();
        $this->provider=$provider;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes=[];
        $code = $this->argument('code');
        if ($code=='italy') {
            $routes = $this->getAllItaly($this->provider);
        } else {
            if($this->provider->checkCode($code)) {
                $routes = $this->getZone($code,$this->provider);
            }
            else {
                $this->error('Bad code.');
            }
        }

        if(count($routes)==0) {
            $this->warn('No routes found');
            return 1;
        } else {
            if($this->option('dry-mode')) {
                $this->info('Running in DRY mode: showing routes ID that would be synced.');
                $this->showRoutes($routes);
            }
            else {
                $this->sync($routes,$this->provider);
            }
        }
    }

    public function showRoutes($routes) {
        if(count($routes)>0) {
            foreach ($routes as $route) {
                $this->info("ID: $route->relation_id / REF: $route->ref");
            }
            $this->info(" ");
            $this->info("Found ".count($routes). " routes to be synced");
        }
    }

    public function sync($routes, Osm2CaiHikingRoutesServiceProvider $provider)
    {
        if (count($routes) > 0) {
            foreach ($routes as $route) {
                $this->info("Sync $route->relation_id (REF:$route->ref)");
                // Retrieve data
                $route_osm = $this->provider->getHikingRoute($route->relation_id);
                if ($route_osm) {
                    // Convert Object to array
                    $route_osm_array = (array)$route_osm;
                    // Map keys
                    $route_cai_array = [];
                    foreach ($route_osm_array as $k => $v) {
                        if ($k == 'relation_id') {
                            ;
                        } else if ($k == 'tags') {
                            // TODO: json data convert
                            // $route_cai_array['tags_osm']=$v;
                        } else if ($k == 'geom') {
                            $route_cai_array['geometry_osm'] = $v;
                        } else {
                            $route_cai_array[$k . '_osm'] = $v;
                        }
                    }
                    $route_cai = HikingRoute::firstOrCreate(['relation_id' => $route->relation_id]);
                    $route_cai->fill($route_cai_array);
                    $route_cai->setOsm2CaiStatus();
                    $route_cai->save();
                }
            }
        }
    }


    public function getAllItaly(Osm2CaiHikingRoutesServiceProvider $provider) {
        $this->info('Sync ALL ITALY.');
        return $provider->getAllRoutes();
    }

    public static function getZone($code, Osm2CaiHikingRoutesServiceProvider $provider) {
        return $provider->getHikingRoutes($code);
    }
}
