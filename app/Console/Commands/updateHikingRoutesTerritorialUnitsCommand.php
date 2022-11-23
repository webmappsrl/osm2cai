<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class updateHikingRoutesTerritorialUnitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-hikingroutes-territorial-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It perform actions on all routes updating territorial units relations';

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

        HikingRoute::all()->map(function(HikingRoute $hr){
            Log::info("Updating territorial units, Route ID:{$hr->id} REF:{$hr->ref}");
            $hr->computeAndSetTerritorialUnits();
            $hr->save();
        });


        return 0;
    }

}
