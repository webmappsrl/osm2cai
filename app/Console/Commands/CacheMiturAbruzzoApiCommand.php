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
        Log::info("Start caching API data for model {$this->argument('model')}");
        $modelClass = App::make("App\\Models\\{$this->argument('model')}");
        $allModels = $modelClass::all();
        foreach ($allModels as $model) {
            Log::info("Processing model with id {$model->id}");
            switch (get_class($modelClass)) {
                case 'App\Models\Region':
                    $this->cacheRegionApiData($model);
                    break;
                case 'App\Models\EcPoi':
                    $this->cacheEcPoiApiData($model);
                    break;
                case 'App\Models\Section':
                    $this->cacheSectionApiData($model);
                    break;
                case 'App\Models\MountainGroups':
                    $this->cacheMountainGroupApiData($model);
                    break;
            }
        }
        Log::info("Finished caching API data for model {$this->argument('model')}");
    }

    protected function cacheRegionApiData($region)
    {
        Log::info("Start caching region $region->name");
        //get osmfeatures data
        $osmfeaturesData = json_decode($region->osmfeatures_data, true);
        $osmfeaturesData = $osmfeaturesData['enrichments']['data'];
        $images = $this->getImagesFromOsmfeaturesData($osmfeaturesData);
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
        $properties['name'] = $region->name ?? '';
        $properties['description'] = $osmfeaturesData['description']['it'] ?? '';
        $properties['abstract'] = $osmfeaturesData['abstract']['it'] ?? '';
        $properties['mountain_groups'] = $mountainGroups;
        $properties['images'] = $images ?? [];

        $geojson['properties'] = $properties;

        //save the geojson in the database
        Log::info("Saving cached API data for region $region->name");
        $region->cached_mitur_api_data = json_encode($geojson);
        $region->save();
        Log::info("Finished caching region $region->name");
    }

    protected function cacheEcPoiApiData($poi)
    {
        Log::info("Start caching poi $poi->name");
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

        Log::info("End caching poi $poi->name");
    }

    protected function cacheSectionApiData($section)
    {
        Log::info("Start caching section $section->name");

        $queryForProvinces = 'SELECT 
        p.id AS province_id, 
        p.name AS province_name, 
        s.id AS section_id, 
        s.name AS section_name
    FROM 
        sections s
    JOIN 
        provinces p 
    ON 
        ST_Intersects(ST_Transform(p.geometry, 4326), s.geometry::geometry)';

        $provinces = DB::select($queryForProvinces);
        //get the province names

        $provincesNames = [];
        foreach ($provinces as $province) {
            $provincesNames[] = $province->province_name;
        }
        //delete double provinces

        $provincesNames = array_unique($provincesNames);

        //implode the provinces
        $provincesNames = implode(', ', $provincesNames);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $section->id;
        $properties['name'] = $section->name;
        $properties['addr:city'] = $section->addr_city ?? '';
        $properties['addr:housenumber'] = $section->addr_housenumber ?? '';
        $properties['addr:postcode'] = $section->addr_postcode ?? '';
        $properties['addr:street'] = $section->addr_street ?? '';
        $properties['provinces'] = $provincesNames;
        $properties['source:ref'] = $section->cai_code;
        $properties['website'] = $section->website ?? '';
        $properties['email'] = $section->email ?? '';
        $properties['opening_hours'] = $section->opening_hours ?? '';
        $properties['phone'] = $section->phone ?? '';
        $properties['wheelchair'] = $section->wheelchair ?? '';
        $properties['fax'] = $section->fax ?? '';
        $properties['images'] = [];

        $geometry = $section->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        //save the geojson in the database so it can be served by the mitur api
        $section->cached_mitur_api_data = json_encode($geojson);
        $section->save();

        Log::info("End caching section $section->name");
    }

    protected function cacheMountainGroupApiData($mountainGroup)
    {

        //decode aggregated_data
        $aggregated_data = json_decode($mountainGroup->aggregated_data, true);

        $regions = DB::table('regions')
            ->select('name')
            ->whereRaw('ST_Intersects(geometry, ?)', [$mountainGroup->geometry])
            ->pluck('name')
            ->toArray();

        $provinces = DB::table('provinces')
            ->select('name')
            ->whereRaw('ST_Intersects(geometry, ?)', [$mountainGroup->geometry])
            ->pluck('name')
            ->toArray();

        $municipalities = DB::table('municipality_boundaries')
            ->select('comune')
            ->whereRaw('ST_Intersects(geom, ?)', [$mountainGroup->geometry])
            ->pluck('comune')
            ->toArray();

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $mountainGroup->id;
        $properties['name'] = $mountainGroup->name ?? 'Nome del gruppo Montuoso';
        $properties['section_ids'] = json_decode($mountainGroup->sections_intersecting, true);
        $properties['area'] = $mountainGroup->getArea() . ' km²';
        $properties['ele_min'] = '856';
        $properties['ele_max'] = '1785';
        $properties['region'] = implode(', ', $regions);
        $properties['provinces'] = implode(', ', $provinces);
        $properties['municipalities'] = implode(', ', $municipalities);
        $properties['map'] = 'https://www.mappa-gruppo-montuoso.it';
        $properties['description'] = $mountainGroup->description ?? '';
        $properties['aggregated_data'] = $mountainGroup->aggregated_data ?? '';
        $properties['protected_area'] = 'Parchi Aree protette Natura 2000';
        $properties['activity'] = 'Escursionismo';
        $properties['hiking_routes'] = json_decode($mountainGroup->hiking_routes_intersecting, true);
        $properties['ec_pois'] = json_decode($mountainGroup->ec_pois_intersecting, true);
        $properties['cai_huts'] = json_decode($mountainGroup->huts_intersecting, true);
        $properties['hiking_routes_map'] = 'https://www.mappa-percorsi.it';
        $properties['disclaimer'] = 'L’escursionismo e, più in generale, l’attività all’aria aperta, è una attività potenzialmente rischiosa: prima di avventurarti in una escursione assicurati di avere le conoscenze e le competenze per farlo. Se non sei sicuro rivolgiti agli esperti locali che ti possono aiutare, suggerire e supportare nella pianificazione e nello svolgimento delle tue attività. I dati non possono garantire completamente la percorribilità senza rischi dei percorsi: potrebbero essersi verificati cambiamenti, anche importanti, dall’ultima verifica effettuata del percorso stesso. E’ fondamentale quindi che chi si appresta a svolgere attività valuti attentamente l’opportunità di proseguire in base ai suggerimenti e ai consigli contenuti, in base alla propria esperienza, alle condizioni metereologiche (anche dei giorni precedenti) e di una valutazione effettuata sul campo all’inizio dello svolgimento della attività. Il Club Alpino Italiano non fornisce garanzie sulla sicurezza dei luoghi descritti, e non si assume alcuna responsabilità per eventuali danni causati dallo svolgimento delle attività descritte.';
        $properties['ec_pois_count'] = $aggregated_data['ec_pois_count'] ?? 0;
        $properties['cai_huts_count'] = $aggregated_data['cai_huts_count'] ?? 0;
        $properties['images'] = ["https://geohub.webmapp.it/storage/ec_media/35934.jpg", "https://ecmedia.s3.eu-central-1.amazonaws.com/EcMedia/Resize/108x137/35933_108x137.jpg"];


        $geojson['properties'] = $properties;
        $geojson['geometry'] = $mountainGroup->getGeometry();

        $mountainGroup->cached_mitur_api_data = json_encode($geojson);
        $mountainGroup->save();
    }


    protected function getImagesFromOsmfeaturesData($osmfeaturesData)
    {
        Log::info("Start getting images from osmfeatures data");
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

        Log::info("End getting images from osmfeatures data");
        return $images;
    }
}
