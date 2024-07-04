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
    protected $signature = 'osm2cai:calculate_intersections {model} {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate intersections between the given model and other models based on GeoIntersectTrait.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $modelClass = "App\\Models\\{$this->argument('model')}";
        $id = $this->argument('id');

        if (!$id) {
            $models = $modelClass::all();
        } else {
            $models = collect([$modelClass::find($id)]);
        }

        $models->each(function ($model) {
            $this->calculateIntersections($model);
        });

        $this->info('Calculations completed.');

        return 0;
    }

    private function calculateIntersections($model)
    {
        if (!($model instanceof \App\Models\CaiHuts))
            $this->calculateHutIntersections($model);

        if (!($model instanceof \App\Models\HikingRoute))
            $this->calculateHikingRouteIntersections($model);

        if (!($model instanceof \App\Models\Section))
            $this->calculateSectionIntersections($model);

        if (!($model instanceof \App\Models\EcPoi))
            $this->calculateEcPoiIntersections($model);

        if (!($model instanceof \App\Models\MountainGroups))
            $this->calculateMountainGroupIntersections($model);

        if ($model instanceof \App\Models\EcPoi) {
            $this->calculateComuneIntersecting($model);
        }


        $model->save();
    }

    private function calculateHutIntersections($model)
    {
        $intersectingHuts = $model->getHutsIntersecting();
        $hutIds = $intersectingHuts->pluck('updated_at', 'id')->toArray();
        $model->huts_intersecting = $hutIds;
    }

    private function calculateHikingRouteIntersections($model)
    {
        if ($model instanceof \App\Models\EcPoi) {
            $intersectingHikingRoutes = $model->getHikingRoutesInBuffer(1000);
            $hikingRouteIds = $intersectingHikingRoutes->pluck('updated_at', 'id')->toArray();
            $model->hiking_routes_in_buffer = $hikingRouteIds;
        } else {
            $intersectingHikingRoutes = $model->getHikingRoutesIntersecting();
            $hikingRouteIds = $intersectingHikingRoutes->pluck('updated_at', 'id')->toArray();
            $model->hiking_routes_intersecting = $hikingRouteIds;
        }
    }

    private function calculateSectionIntersections($model)
    {
        $intersectingSections = $model->getSectionsIntersecting();
        $sectionIds = $intersectingSections->pluck('updated_at', 'id')->toArray();
        $model->sections_intersecting = $sectionIds;
    }

    private function calculateEcPoiIntersections($model)
    {
        $intersectingEcPois = $model->getPoisIntersecting();
        $ecPoiIds = $intersectingEcPois->pluck('updated_at', 'id')->toArray();
        $model->ec_pois_intersecting = $ecPoiIds;
    }

    private function calculateMountainGroupIntersections($model)
    {
        $intersectingMountainGroups = $model->getMountainGroupsIntersecting(4);
        $mountainGroupIds = $intersectingMountainGroups->pluck('updated_at', 'id')->toArray();
        $model->mountain_groups_intersecting = $mountainGroupIds;
    }

    private function calculateComuneIntersecting($model)
    {
        $intersectingMunicipalities = $model->getMunicipalityIntersecting();
        $comuni = $intersectingMunicipalities->pluck('comune')->implode(',');
        $model->comuni = $comuni;
    }
}