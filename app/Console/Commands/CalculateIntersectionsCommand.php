<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculateIntersectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:calculate_intersections {model?} {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate intersections between the given model and the other models based on the trait GeoIntersectTrait.';

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
        $model = $this->argument('model');
        $id = $this->argument('id');

        if ($model) {
            $model = "App\\Models\\$model";
            if (!app($model) instanceof \App\Models\MountainGroups) {
                $this->error("Only model MountainGroups is supported at the moment.");
                return 1;
            }
            if ($id) {
                $model = $model::find($id);
                try {
                    $this->info("Calculating intersections for  $model->id...");
                    Log::info("Calculating intersections for  $model->id...");

                    $hikingRoutes = $model->getHikingRoutesIntersecting();
                    $this->info("Hiking routes: " . $hikingRoutes->count());
                    Log::info("Hiking routes: " . $hikingRoutes->count());

                    $huts = $model->getHutsIntersecting();
                    $this->info("Huts: " . $huts->count());
                    Log::info("Huts: " . $huts->count());

                    $sections = $model->getSectionsIntersecting();
                    $this->info("Sections: " . $sections->count());
                    Log::info("Sections: " . $sections->count());

                    $ecPois = $model->getPoisIntersecting();
                    $this->info("EC POIs: " . $ecPois->count());
                    Log::info("EC POIs: " . $ecPois->count());
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    return 1;
                }

                $hikingRoutesIds = $hikingRoutes->pluck('updated_at', 'id')->toArray();
                $model->hiking_routes_intersecting = $hikingRoutesIds;

                $hutsIds = $huts->pluck('id')->toArray();
                $model->huts_intersecting = $hutsIds;

                $sectionsIds = $sections->pluck('id')->toArray();
                $model->sections_intersecting = $sectionsIds;

                $ecPoisIds = $ecPois->pluck('id')->toArray();
                $model->ec_pois_intersecting = $ecPoisIds;

                $model->save();
            } else {
                foreach ($model::all() as $model) {
                    $this->info("Calculating intersections for model $model->id...");
                    Log::info("Calculating intersections for model $model->id...");
                    $hikingRoutes = $model->getHikingRoutesIntersecting();
                    $this->info("Hiking routes: " . $hikingRoutes->count());
                    Log::info("Hiking routes: " . $hikingRoutes->count());

                    $huts = $model->getHutsIntersecting();
                    $this->info("Huts: " . $huts->count());
                    Log::info("Huts: " . $huts->count());

                    $sections = $model->getSectionsIntersecting();
                    $this->info("Sections: " . $sections->count());
                    Log::info("Sections: " . $sections->count());

                    $ecPois = $model->getPoisIntersecting();
                    $this->info("EC POIs: " . $ecPois->count());
                    Log::info("EC POIs: " . $ecPois->count());

                    $hikingRoutesIds = $hikingRoutes->pluck('updated_at', 'id')->toArray();
                    $model->hiking_routes_intersecting = $hikingRoutesIds;

                    $hutsIds = $huts->pluck('id')->toArray();
                    $model->huts_intersecting = $hutsIds;

                    $sectionsIds = $sections->pluck('id')->toArray();
                    $model->sections_intersecting = $sectionsIds;

                    $ecPoisIds = $ecPois->pluck('id')->toArray();
                    $model->ec_pois_intersecting = $ecPoisIds;

                    $model->save();
                }
            }

            $this->info("Intersections calculated successfully.");
            Log::info("Intersections calculated successfully.");

            return 0;
        } else {
            $this->error("Model is required.");
            Log::error("Model is required.");

            return 1;
        }
    }
}
