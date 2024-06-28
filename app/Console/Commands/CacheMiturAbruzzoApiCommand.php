<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
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
    protected $description = 'Perform an API call to the MITUR Abruzzo API and store the response in the database';

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
                default:
                    break;
            }
        }
    }

    protected function cacheRegionApiData($region)
    {
        //get osmfeatures data
        $osmfeaturesData = json_decode($region->osmfeatures_data, true);
        $osmfeaturesData = json_decode($osmfeaturesData['enrichment']['data'], true);
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
        $properties['mountain_groups'] = $mountainGroups;
        $properties['images'] = $osmfeaturesData['images'] ?? [];


        $geojson['properties'] = $properties;

        //save the geojson in the database
        $region->cached_mitur_api_data = json_encode($geojson);
        $region->save();
    }
}
