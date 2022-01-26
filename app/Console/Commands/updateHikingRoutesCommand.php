<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class updateHikingRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-hikingroutes 
                            {--ids= : comma separated values of hiking routes that must be updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It perform actions on specific route(s) identified by regions, areas, sectors, ids';

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
        // Build ids
        $ids=explode(',',$this->option('ids'));
        if(count($ids)>0) {
            foreach($ids as $id) {
                $route = HikingRoute::find($id);
                if (!empty($route)) {
                    Log::info("Processing Route ID:$id REF:{$route->ref}");
                    $route->computeAndSetTechInfo();
                    $route->save();
                } else {
                    Log::info("No route found with id $id ... Skipping");
                }
            }
        } else {
            Log::info("No routes selected use --ids parameter.");
        }
        return 0;
    }

}
