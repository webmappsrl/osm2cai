<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use App\Providers\Osm2CaiHikingRoutesServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Osm2CaiSyncHinkingRoute extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:sync
                                {osmid? : Hiking Route OSMID to be synced. Leave it blank to sync all already existing routes} 
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync specific route already in DB (hiking_routes table).';

    private $provider;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Osm2CaiHikingRoutesServiceProvider $provider)
    {
        parent::__construct();
        $this->provider = $provider;

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->hasArgument('osmid') && !is_null($this->argument('osmid'))) {
            Log::info("Sync {$this->argument('osmid')}");
            $this->provider->syncHikingRoute($this->argument('osmid'));
        } else {
            Log::info("Sync all routes");
            if (HikingRoute::count() > 0) {
                Log::info('Need to sync ' . HikingRoute::count() . ' hiking routes.');
                foreach (HikingRoute::select('relation_id')->get() as $route) {
                    $this->provider->syncHikingRoute($route->relation_id);
                }
            } else {
                Log::warning('No routes found in DB!');
            }
        }
        return 0;
    }
}
