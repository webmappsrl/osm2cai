<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use App\Models\NaturalSpring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNaturalSpringHR extends Command
{
    protected $signature =
    'osm2cai:add_natural_springs_to_hiking_routes 
                            {model : The model to sync with (HikingRoute, CaiHuts)} 
                            {id? : The ID of the model to specifically sync}';

    protected $description = 'Updates hiking routes with nearby Natural Springs';

    public function handle()
    {
        $buffer = config('osm2cai.hiking_route_buffer');
        $model = $this->argument('model');
        $id = $this->argument('id');

        switch ($model) {
            case 'NaturalSpring':
                $this->syncNaturalSprings($buffer);
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

    protected function syncNaturalSprings($buffer)
    {
        if ($this->argument('id')) {
            $model = NaturalSpring::find($this->argument('id'));

            if (!$model->geometry) {
                Log::warning("Cai hut {$model->id} has no geometry");
                return;
            }
            $geometryEWKB = $model->geometry;

            $nearbyRoutes = HikingRoute::select('id', 'geometry', 'cai_huts', 'has_cai_huts')
                ->whereRaw("ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, ST_GeomFromEWKB(decode(?, 'hex')), ?)", [$geometryEWKB, $buffer])
                ->get();

            foreach ($nearbyRoutes as $route) {
                $currentSprings = json_decode($route->natural_springs, true) ?: [];
                if (!in_array($model->id, $currentSprings)) {
                    array_push($currentSprings, $model->id);
                    $route->update([
                        'natural_springs' => json_encode($currentSprings),
                        'has_natural_springs' => true,
                    ]);
                }
            }
        } else {
            $models = DB::table('natural_springs')
                ->select(['*'])
                ->get();

            foreach ($models as $model) {
                if (!$model->geometry) {
                    Log::warning("Cai hut {$model->id} has no geometry");
                    return;
                }
                $geometryEWKB = $model->geometry;

                $nearbyRoutes = HikingRoute::select('id', 'geometry', 'cai_huts', 'has_cai_huts')
                    ->whereRaw("ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, ST_GeomFromEWKB(decode(?, 'hex')), ?)", [$geometryEWKB, $buffer])
                    ->get();

                foreach ($nearbyRoutes as $route) {
                    $currentSprings = json_decode($route->natural_springs, true) ?: [];
                    if (!in_array($model->id, $currentSprings)) {
                        array_push($currentSprings, $model->id);
                        $route->update([
                            'natural_springs' => json_encode($currentSprings),
                            'has_natural_springs' => true,
                        ]);
                    }
                }
            }
        }
    }

    protected function syncHikingRoutes($buffer)
    {
        if ($this->argument('id')) {
            $model = \App\Models\HikingRoute::where('id', $this->argument('id'))->first();
            Log::info("Hiking route {$model->id}");
            if (!$model->geometry) {
                Log::warning("Hiking route {$model->id} has no geometry");
                return;
            }
            $nearbySpringIds = DB::select(DB::raw("SELECT natural_springs.id 
                                        FROM natural_springs, hiking_routes 
                                        WHERE hiking_routes.id = :routeId 
                                        AND ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, natural_springs.geometry, :buffer)"), [
                'routeId' => $model->id,
                'buffer' => $buffer
            ]);

            $nearbySpringIds = array_map(function ($spring) {
                return $spring->id;
            }, $nearbySpringIds);

            $hr = HikingRoute::find($model->id);
            Log::info("Hiking route {$hr->id} has " . count($nearbySpringIds) . " nearby springs");


            $currentSprings = json_decode($hr->natural_springs, true) ?: [];
            sort($currentSprings);
            sort($nearbySpringIds);


            if ($currentSprings !== $nearbySpringIds || $hr->has_natural_springs !== (count($nearbySpringIds) > 0))
                Log::info("Hiking route {$hr->id} has changed");
            $hr->natural_springs = json_encode($nearbySpringIds);
            $hr->has_natural_springs = count($nearbySpringIds) > 0;

            $hr->is_syncing = true;
            $hr->save();
        } else {
            $models = DB::table('hiking_routes')
                ->select(['id', 'natural_springs', 'has_natural_springs', 'geometry'])
                ->get();

            foreach ($models as $model) {
                Log::info("Hiking route {$model->id}");
                if (!$model->geometry) {
                    Log::warning("Hiking route {$model->id} has no geometry");
                    return;
                }
                $nearbySpringIds = DB::select(DB::raw("SELECT natural_springs.id 
                                        FROM natural_springs, hiking_routes 
                                        WHERE hiking_routes.id = :routeId 
                                        AND ST_DWithin(ST_SetSRID(hiking_routes.geometry, 4326)::geography, natural_springs.geometry, :buffer)"), [
                    'routeId' => $model->id,
                    'buffer' => $buffer
                ]);

                $nearbySpringIds = array_map(function ($spring) {
                    return $spring->id;
                }, $nearbySpringIds);

                $hr = HikingRoute::find($model->id);
                Log::info("Hiking route {$hr->id} has " . count($nearbySpringIds) . " nearby springs");


                $currentSprings = json_decode($hr->natural_springs, true) ?: [];
                sort($currentSprings);
                sort($nearbySpringIds);


                if ($currentSprings !== $nearbySpringIds || $hr->has_natural_springs !== (count($nearbySpringIds) > 0))
                    Log::info("Hiking route {$hr->id} has changed");
                $hr->natural_springs = json_encode($nearbySpringIds);
                $hr->has_natural_springs = count($nearbySpringIds) > 0;

                $hr->is_syncing = true;
                $hr->save();
            }
        }
    }
}
