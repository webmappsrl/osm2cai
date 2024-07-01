<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\PoiMapController;
use App\Http\Controllers\V2\MiturAbruzzoController;

class CacheMiturAbruzzoApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:cache-mitur-abruzzo-api {model=Region : The model name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store MITUR Abruzzo API data in the database';

    protected $usage = 'osm2cai:cache-mitur-abruzzo-api {model=Region? : The model name}';

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
        ini_set('memory_limit', '2048M');
        $modelClass = App::make("App\\Models\\{$this->argument('model')}");
        $allModels = $modelClass::all();
        foreach ($allModels as $model) {

            switch (get_class($modelClass)) {
                case 'App\Models\Region':
                    $this->cacheRegionApiData($model);
                    break;
                case 'App\Models\EcPoi':
                    $this->cacheEcPoiApiData($model);
                    break;
            }
        }
    }

    protected function cacheRegionApiData($region)
    {
        //get osmfeatures data
        $osmfeaturesData = json_decode($region->osmfeatures_data, true);
        $osmfeaturesData = json_decode($osmfeaturesData['enrichment']['data'], true);
        $images = [];
        foreach ($osmfeaturesData['images'] as $image) {
            // add only $image['source_url'] with extension jpg, jpeg, png, bmp, gif, webp, svg (to avoid other files)
            if (in_array(pathinfo($image['source_url'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp', 'svg'])) {
                $images[] = $image['source_url'];
            }
        }
        //get the mountain groups for the region
        $mountainGroups = $region->mountainGroups;
        //format the date
        $mountainGroups = $mountainGroups->mapWithKeys(function ($mountainGroup) {
            $formattedDate = $mountainGroup->updated_at ? $mountainGroup->updated_at->toIso8601String() : null;

            return [$mountainGroup->id => $formattedDate];
        });

        //get the region geometry
        $geom_s = $region
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;
        $geom = json_decode($geom_s, TRUE);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';
        $geojson['properties'] = [];
        $geojson['geometry'] = $geom;

        $properties = [];
        $properties['id'] = $region->id;
        $properties['name'] = $region->name ?? 'Nome della Regione';
        $properties['abstract'] = $osmfeaturesData['abstract']['it'] ?? 'Abstract della Regione';
        $properties['description'] = $osmfeaturesData['description']['it'] ?? 'Descrizione della Regione';
        $properties['mountain_groups'] = $mountainGroups;
        $properties['images'] = $images ?? [];

        $geojson['properties'] = $properties;

        //save the geojson in the database
        $region->cached_mitur_api_data = json_encode($geojson);
        $region->save();
    }

    protected function cacheEcPoiApiData($poi)
    {
        if (!$poi->osmfeatures_data) {
            $this->info("No osmfeatures data for poi $poi->name");
            Log::info("No osmfeatures data for poi $poi->name");
            $osmfeaturesData = [];
        } else {
            $osmfeaturesData = json_decode($poi->osmfeatures_data, true);
            if ($osmfeaturesData['enrichments']) {
                $osmfeaturesData = $osmfeaturesData['enrichments']['data'];
            } else {
                Log::info("No osmfeatures data for poi $poi->name");
                $this->info("No osmfeatures data for poi $poi->name");
                $osmfeaturesData = [];
            }
        }

        $images = $this->getImagesFromOsmfeaturesData($osmfeaturesData);

        $hikingRoutes = json_decode($poi->hiking_routes_in_buffer, true);
        $hikingRoute = $hikingRoutes ? HikingRoute::find(array_key_first($hikingRoutes)) : null;

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $poi->id;
        $properties['name'] = $osmfeaturesData['name'] ?? $poi->name;
        $properties['type'] = $poi->getTagsMapping();
        $properties['comune'] = $poi->comuni ?? '';
        $properties['description'] = $osmfeaturesData['description']['it'] ?? "";
        $properties['info'] = $osmfeaturesData['abstract']['it'] ?? "";
        $properties['difficulty'] = $hikingRoute ? $hikingRoute->cai_scale : '';
        $properties['activity'] = 'Escursionismo';
        $properties['has_hiking_routes'] = $hikingRoutes;
        $properties['map'] = route('poi-map', ['id' => $poi->id]);
        $properties['images'] = $images ?? [];

        $geometry = $poi->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        //save the geojson in the database so it can be served by the mitur api
        $poi->cached_mitur_api_data = json_encode($geojson);
        $poi->save();
    }

    protected function getImagesFromOsmfeaturesData($osmfeaturesData)
    {
        $images = [];
        if (!isset($osmfeaturesData['images'])) {
            return $images;
        }
        foreach ($osmfeaturesData['images'] as $image) {
            // add only $image['source_url'] with extension jpg, jpeg, png, bmp, gif, webp, svg (to avoid other files)
            if (in_array(pathinfo($image['source_url'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp', 'svg'])) {
                $images[] = $image['source_url'];
            }
        }

        return $images;
    }
}