<?php

namespace App\Console\Commands;

use App\Models\CaiHuts;
use App\Models\HikingRoute;
use App\Models\NaturalSpring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SyncHutsHR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:add_cai_huts_to_hiking_routes 
                            {model : The model to sync with (HikingRoute, CaiHuts)} 
                            {id? : The ID of the model to specifically sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates hiking routes with nearby Cai Huts or vice versa';

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
        $buffer = config('osm2cai.hiking_route_buffer');
        $model = $this->argument('model');
        $id = $this->argument('id');

        switch ($model) {
            case 'CaiHuts':
                $this->syncCaiHuts($buffer);
                break;
            case 'HikingRoute':
                $this->syncHikingRoutes($buffer);
                break;
            default:
                $this->error('Invalid model');
                break;
        }
        $this->info('Sync completed');
    }

    protected function syncHikingRoutes($buffer)
    {
        if ($this->argument('id')) {
            $models = collect(DB::table('hiking_routes')->select(['id', 'cai_huts', 'has_cai_huts', 'geometry'])->where('id', $this->argument('id'))->get());
        } else {
            $models = DB::table('hiking_routes')
                ->select(['id', 'cai_huts', 'has_cai_huts', 'geometry'])
                ->get();
        }
        foreach ($models as $model) {
            if (!$model->geometry) {
                Log::warning("Hiking route {$model->id} has no geometry");
                return;
            }
            $nearbyHutsIds = DB::select(DB::raw("SELECT cai_huts.id 
                                    FROM cai_huts, hiking_routes 
                                    WHERE hiking_routes.id = :routeId 
                                    AND ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, cai_huts.geometry, :buffer)"), [
                'routeId' => $model->id,
                'buffer' => $buffer
            ]);

            $nearbyHutsIds = array_map(
                function ($hut) {
                    return $hut->id;
                },
                $nearbyHutsIds
            );

            $hr = HikingRoute::find($model->id);

            $currentHuts = json_decode($hr->cai_huts, true) ?: [];
            sort($currentHuts);
            sort($nearbyHutsIds);

            //only save if there is a change so the event observer in the hiking route model will not be triggered [app\Models\HikingRoute line 100]
            if ($currentHuts !== $nearbyHutsIds || $hr->has_cai_huts !== (count($nearbyHutsIds) > 0)) {
                $hr->cai_huts = json_encode($nearbyHutsIds);
                $hr->has_cai_huts = count($nearbyHutsIds) > 0;
                $hr->is_syncing = true;
                $hr->save();
            }
        }
    }

    protected function syncCaiHuts($buffer)
    {
        if ($this->argument('id')) {
            $model = CaiHuts::where('id', $this->argument('id'))->first();

            if (!$model->geometry) {
                Log::warning("Cai hut {$model->id} has no geometry");
                return;
            }
            $geometryEWKB = $model->geometry;

            $nearbyRoutes = HikingRoute::select('id', 'geometry', 'cai_huts', 'has_cai_huts')
                ->whereRaw("ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, ST_GeomFromEWKB(decode(?, 'hex')), ?)", [$geometryEWKB, $buffer])
                ->get();


            foreach ($nearbyRoutes as $route) {
                $hr = HikingRoute::find($route->id);
                $currentHuts = json_decode($hr->cai_huts, true) ?: [];
                if (!in_array($model->id, $currentHuts)) {
                    array_push($currentHuts, $model->id);
                    $hr->update([
                        'cai_huts' => json_encode($currentHuts),
                        'has_cai_huts' => true,
                    ]);
                }
            }
        } else {
            $models = DB::table('cai_huts')
                ->select(['*'])
                ->get();

            foreach ($models as $model) {
                if (!$model->geometry) {
                    Log::warning("Cai hut {$model->id} has no geometry");
                    continue;
                }
                $geometryEWKB = $model->geometry;

                $nearbyRoutes = HikingRoute::select('id', 'geometry, cai_huts, has_cai_huts')
                    ->whereRaw("ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, ST_GeomFromEWKB(decode(?, 'hex')), ?)", [$geometryEWKB, $buffer])
                    ->get();

                foreach ($nearbyRoutes as $route) {
                    $hr = HikingRoute::find($route->id);
                    $currentHuts = json_decode($hr->cai_huts, true) ?: [];
                    if (!in_array($model->id, $currentHuts)) {
                        array_push($currentHuts, $model->id);
                        $hr->update([
                            'cai_huts' => json_encode($currentHuts),
                            'has_cai_huts' => true,
                        ]);
                    }
                }
            }
        }
    }
}
