<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use App\Services\OsmService;

class CheckHikingRoutesGeometry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:check_hiking_routes_geometry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterates over all hiking routes to populate geometry_check attribute checking the geometry api';

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
        HikingRoute::all()->map( function($hr) {
            $this->info("Checking {$hr->name} (id:{$hr->id})");
            $hr->geometry_check = $hr->hasCorrectGeometry();
            $hr->save();
        } );

        return 0;
    }
}
