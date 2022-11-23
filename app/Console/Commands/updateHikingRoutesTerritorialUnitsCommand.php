<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            try {
                $this->info("Updating territorial units, Route ID:{$hr->id} REF:{$hr->ref}");
                $hr->computeAndSetTerritorialUnits();
                $hr->save();
            }
            catch( Exception|Throwable $e)
            {
                $this->error($e->getMessage());
                Log::error($e->getMessage());
            }

        });


        return 0;
    }

}
