<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use App\Services\OsmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateSyncBrokenGeometries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-sync-broken-geometries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'The command check and fix routes with broken geometries (without all ways)';

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
        $logger = Log::channel('sync');

        /**
         * @var \App\Services\OsmService
         */
        $osmService = app()->make(OsmService::class);
        $connection = DB::connection();
        $relations = $connection->select('Select id, parts from planet_osm_rels');
        foreach ($relations as $relation) {
            $relationId = $relation->id;

            preg_match_all('#\d+#', $relation->parts, $parts);
            $parts = collect($parts[0]);

            $waysRelated = $connection->table('hiking_ways_osm')->whereIn('way_id', $parts)->get();

            $message = "Relation id $relationId";
            if ($waysRelated->unique()->count() !== $parts->unique()->count()) {
                $waysRelatedIds = $waysRelated->pluck('way_id')->all();
                $waysDiff = $parts->diff($waysRelatedIds)->implode(', ');
                $waysOk = $parts->intersect($waysRelatedIds)->implode(', ');

                $message .= " ways missing: $waysDiff | ways ok: $waysOk";
                $this->error("$message");
                $logger->warning($message);


                $hr = HikingRoute::firstWhere('relation_id', $relationId);
                if ($hr) {
                    $osmGeo = $osmService->getHikingRouteGeometry($relationId);
                    $hr->geometry = $osmGeo;
                    $message = "Hiking route model {$hr->id} updated via osm api sync";
                    $hr->save();
                    $logger->info($message);
                    $this->info($message);
                } else {
                    $message = "Impossible found an hiking route model with relation id $relationId";
                    $logger->error($message);
                    $this->error($message);
                }
            } else {
                //$this->line("$message OK" );
            }
        }
    }
}