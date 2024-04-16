<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

        if ($model && $id) {
            $model = "App\\Models\\$model";
            $model = $model::find($id);
            if ($model) {
                if ($model instanceof \App\Models\MountainGroups) {
                    try {
                        $hikingRoutes = $model->getHikingRoutesIntersecting();
                        $huts = $model->getHutsIntersecting();
                        $sections = $model->getSectionsIntersecting();
                        $ecPois = $model->getPoisIntersecting();
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
                }
            }
        } else {
            $this->error("Only model MountainGroups is supported at the moment.");
        }

        return 0;
    }
}
