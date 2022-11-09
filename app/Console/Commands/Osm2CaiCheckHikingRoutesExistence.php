<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use App\Services\OsmService;

class Osm2CaiCheckHikingRoutesExistence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:check_hikig_routes_existence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterates over all hiking routes to populate deleted_on_osm attribute checking osm2cai api';

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
    public function handle()
    {
        $service = new OsmService();
        HikingRoute::where('deleted_on_osm',false)->get()->map( function($hr) use ($service) {
            $this->info("Checking {$hr->name} (id:{$hr->id} | relation_id:{$hr->relation_id})");
            if ( $service->hikingRouteExists( $hr->relation_id ) === false )
            {
                $hr->deleted_on_osm = true;
                $hr->save();
                $this->warn("Deleted {$hr->id}");
            }
        } );

        return 0;
    }
}
