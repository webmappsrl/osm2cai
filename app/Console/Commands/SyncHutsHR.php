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
            $model = \App\Models\HikingRoute::where('id', $this->argument('id'))->first();
            if (!$model->geometry) {
                Log::warning("Hiking route {$model->id} has no geometry");
                return;
            }
            $nearbyHutsIds = DB::select(DB::raw("SELECT cai_huts.id 
                                        FROM cai_huts, hiking_routes 
                                        WHERE hiking_routes.id = :routeId 
                                        AND ST_DWithin(hiking_routes.geometry, cai_huts.geometry, :buffer)"), [
                'routeId' => $model->id,
                'buffer' => $buffer
            ]);

            $nearbyHutsIds = array_map(function ($hut) {
                return $hut->id;
            }, $nearbyHutsIds);

            $currentHuts = json_decode($model->cai_huts, true) ?: [];
            sort($currentHuts);
            sort($nearbyHutsIds);

            $hr = \App\Models\HikingRoute::find($model->id);

            if ($currentHuts !== $nearbyHutsIds || $model->has_cai_huts !== (count($nearbyHutsIds) > 0))
                $model->cai_huts = json_encode($nearbyHutsIds);
            $model->has_cai_huts = count($nearbyHutsIds) > 0;

            $model->is_syncing = true;
            $model->save();
        } else {
            $models = DB::table('hiking_routes')
                ->select(['id', 'cai_huts', 'has_cai_huts', 'geometry'])
                ->get();

            foreach ($models as $model) {
                if (!$model->geometry) {
                    Log::warning("Hiking route {$model->id} has no geometry");
                    continue;
                }
                $nearbyHutsIds = DB::select(DB::raw("SELECT cai_huts.id 
                                        FROM cai_huts, hiking_routes 
                                        WHERE hiking_routes.id = :routeId 
                                        AND ST_DWithin(hiking_routes.geometry, cai_huts.geometry, :buffer)"), [
                    'routeId' => $model->id,
                    'buffer' => $buffer
                ]);

                $nearbyHutsIds = array_map(function ($hut) {
                    return $hut->id;
                }, $nearbyHutsIds);

                $hr = \App\Models\HikingRoute::find($model->id);

                $hr->cai_huts = json_encode($nearbyHutsIds);
                $hr->has_cai_huts = count($nearbyHutsIds) > 0;
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
                $currentHuts = json_decode($route->cai_huts, true) ?: [];
                if (!in_array($model->id, $currentHuts)) {
                    array_push($currentHuts, $model->id);
                    $route->update([
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

                $nearbyRoutes = HikingRoute::select('id', 'geometry')
                    ->whereRaw("ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, ST_GeomFromEWKB(decode(?, 'hex')), ?)", [$geometryEWKB, $buffer])
                    ->get();

                foreach ($nearbyRoutes as $route) {
                    $currentHuts = json_decode($route->cai_huts, true) ?: [];
                    if (!in_array($model->id, $currentHuts)) {
                        array_push($currentHuts, $model->id);
                        $route->update([
                            'cai_huts' => json_encode($currentHuts),
                            'has_cai_huts' => true,
                        ]);
                    }
                }
            }
        }
    }
}
