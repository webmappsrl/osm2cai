<?php

namespace App\Console\Commands;

use App\Models\CaiHuts;
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
    protected $signature = 'osm2cai:cache-mitur-abruzzo-api {model=Region : The model name} {id? : The model id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store MITUR Abruzzo API data in the database';


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
        $allModels = $this->argument('id')
            ? [$modelClass::find($this->argument('id'))]
            : (class_basename($modelClass) === 'HikingRoute'
                ? $modelClass::where('osm2cai_status', 4)->get() //get only SDA 4
                : $modelClass::all());

        $this->withProgressBar($allModels, function ($model) use ($modelClass) {
            $className = class_basename($modelClass);
            Log::info("Processing model of class {$className} with id {$model->id}");
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
                case 'App\Models\HikingRoute':
                    $this->cacheHikingRouteApiData($model);
                    break;
                case 'App\Models\CaiHuts':
                    $this->cacheCaiHutsApiData($model);
                    break;
            }
        });

        Log::info("Finished caching API data for model {$this->argument('model')}");
    }

    protected function cacheCaiHutsApiData($hut)
    {
        //get the mountain groups for the hut based on the geometry intersection
        $mountainGroups = $hut->getMountainGroupsIntersecting()->first();

        //get the pois in a 1km buffer from the hut
        $pois = $hut->getPoisInBuffer(1000);

        //get the hiking routes in a 1km buffer from the hut
        $hikingRoutes = $hut->getHikingRoutesInBuffer(1000);

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $hut->id;
        $properties['name'] = $hut->second_name ?? $hut->name ?? '';
        $properties['type'] = explode(' ', $hut->second_name)[0] ?? '';
        $properties['elevation'] = $hut->elevation ?? '';
        $properties['mountain_groups'] = $mountainGroups ? $mountainGroups->id : '';
        $properties['type_custodial'] = $hut->type_custodial ?? '';
        $properties['company_management_property'] = $hut->company_management_property ?? '';
        $properties['addr:street'] = $hut->addr_street ?? '';
        $properties['addr:housenumber'] = $hut->addr_housenumber ?? '';
        $properties['addr:postcode'] = $hut->addr_postcode ?? '';
        $properties['addr:city'] = $hut->addr_city ?? '';
        $properties['ref:vatin'] = $hut->ref_vatin ?? '';
        $properties['phone'] = $hut->phone ?? '';
        $properties['fax'] = $hut->fax ?? '';
        $properties['email'] = $hut->email ?? '';
        $properties['email_pec'] = $hut->email_pec ?? '';
        $properties['website'] = $hut->website ?? '';
        $properties['facebook_contact'] = $hut->facebook_contact ?? '';
        $properties['municipality_geo'] = $hut->municipality_geo ?? '';
        $properties['province_geo'] = $hut->province_geo ?? '';
        $properties['site_geo'] = $hut->site_geo ?? '';
        $properties['source:ref'] = $hut->unico_id;
        $properties['description'] = $hut->description ?? '';
        $properties['pois'] = $pois->count() > 0 ? $pois->pluck('updated_at', 'id')->toArray() : [];
        $properties['opening'] = $hut->opening ?? "";
        $properties['acqua_in_rifugio_service'] = $hut->acqua_in_rifugio_serviced ?? '';
        $properties['acqua_calda_service'] = $hut->acqua_calda_service ?? '';
        $properties['acqua_esterno_service'] = $hut->acqua_esterno_service ?? '';
        $properties['posti_letto_invernali_service'] = $hut->posti_letto_invernali_service ?? '';
        $properties['posti_totali_service'] = $hut->posti_totali_service ?? '';
        $properties['ristorante_service'] = $hut->ristorante_service ?? '';
        $properties['activity'] = $hut->activities ?? 'Escursionismo,Alpinismo';
        $properties['necessary_equipment'] = $hut->necessary_equipment ?? 'Normale dotazione Escursionistica / Normale dotazione Alpinistica';
        $properties['rates'] = $hut->rates ?? 'https://www.cai.it/wp-content/uploads/2022/12/23-2022-Circolare-Tariffario-rifugi-2023_signed.pdf';
        $properties['payment_credit_cards'] = $hut->payment_credit_cards ?? '1';
        $properties['hiking_routes'] = $hikingRoutes->count() > 0 ? $hikingRoutes->pluck('updated_at', 'id')->toArray() : [];
        $properties['accessibilitá_ai_disabili_service'] = $hut->acessibilitá_ai_disabili_service ?? '';
        $properties['rule'] = $hut->rule ?? 'https://www.cai.it/wp-content/uploads/2020/12/Regolamento-strutture-ricettive-del-Club-Alpino-Italiano-20201.pdf';
        $properties['map'] = route('cai-huts-map', ['id' => $hut->id]);
        $properties['images'] = [];

        $geometry = $hut->getGeometry();

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        $hut->cached_mitur_api_data = json_encode($geojson);
        $hut->save();

        Log::info("End caching hut $hut->id");
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
        $properties['mountain_groups'] = $mountainGroups;

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
            Log::info("No osmfeatures data for poi $poi->name");
            $osmfeaturesData = [];
        } else {
            $osmfeaturesData = json_decode($poi->osmfeatures_data, true);
            if ($osmfeaturesData['enrichments']) {
                $osmfeaturesData = $osmfeaturesData['enrichments']['data'];
            } else {
                Log::info("No osmfeatures data for poi $poi->name");
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
        $properties['area'] = $mountainGroup->getArea();
        $properties['ele_min'] = $mountainGroup->elevation_min ?? '';
        $properties['ele_max'] = $mountainGroup->elevation_max ?? '';
        $properties['ele_avg'] = $mountainGroup->elevation_avg ?? '';
        $properties['ele_stdev'] = $mountainGroup->elevation_stddev ?? '';
        $properties['slope_min'] = $mountainGroup->slope_min ?? '';
        $properties['slope_max'] = $mountainGroup->slope_max ?? '';
        $properties['slope_avg'] = $mountainGroup->slope_avg ?? '';
        $properties['slope_stdev'] = $mountainGroup->slope_stddev ?? '';
        $properties['region'] = implode(', ', $regions);
        $properties['provinces'] = implode(', ', $provinces);
        $properties['municipalities'] = implode(', ', $municipalities);
        $properties['map'] = route('mountain-groups-map', ['id' => $mountainGroup->id]);
        $properties['description'] = $mountainGroup->description ?? '';
        $properties['protected_area'] = 'Parchi Aree protette Natura 2000';
        $properties['activity'] = 'Escursionismo';
        $properties['hiking_routes'] = json_decode($mountainGroup->hiking_routes_intersecting, true);
        $properties['ec_pois'] = json_decode($mountainGroup->ec_pois_intersecting, true);
        $properties['cai_huts'] = json_decode($mountainGroup->huts_intersecting, true);
        $properties['hiking_routes_map'] = route('mountain-groups-hr-map', ['id' => $mountainGroup->id]);
        $properties['disclaimer'] = 'L’escursionismo e, più in generale, l’attività all’aria aperta, è una attività potenzialmente rischiosa: prima di avventurarti in una escursione assicurati di avere le conoscenze e le competenze per farlo. Se non sei sicuro rivolgiti agli esperti locali che ti possono aiutare, suggerire e supportare nella pianificazione e nello svolgimento delle tue attività. I dati non possono garantire completamente la percorribilità senza rischi dei percorsi: potrebbero essersi verificati cambiamenti, anche importanti, dall’ultima verifica effettuata del percorso stesso. E’ fondamentale quindi che chi si appresta a svolgere attività valuti attentamente l’opportunità di proseguire in base ai suggerimenti e ai consigli contenuti, in base alla propria esperienza, alle condizioni metereologiche (anche dei giorni precedenti) e di una valutazione effettuata sul campo all’inizio dello svolgimento della attività. Il Club Alpino Italiano non fornisce garanzie sulla sicurezza dei luoghi descritti, e non si assume alcuna responsabilità per eventuali danni causati dallo svolgimento delle attività descritte.';
        $properties['images'] = [];


        $geojson['properties'] = $properties;
        $geojson['geometry'] = $mountainGroup->getGeometry();

        $mountainGroup->cached_mitur_api_data = json_encode($geojson);
        $mountainGroup->save();

        Log::info("End caching mountain group $mountainGroup->name");
    }

    protected function cacheHikingRouteApiData($hikingRoute)
    {

        if ($hikingRoute->osm2cai_status != 4) {
            Log::info("Skip caching hiking route $hikingRoute->name: status $hikingRoute->osm2cai_status");
            return;
        }
        //get the pois intersecting with the hiking route
        $pois = $hikingRoute->getPoisIntersecting();

        $tdh = $hikingRoute->tdh;

        $geometry = $hikingRoute->getGeometry();

        if (!is_null($geometry) && isset($geometry['coordinates'][0]) && is_array($geometry['coordinates'][0])) {
            $firstCoordinate = $geometry['coordinates'][0][0] ?? null;
            $lastCoordinate = $geometry['coordinates'][0][count($geometry['coordinates'][0]) - 1] ?? null;

            if (is_array($firstCoordinate) && is_array($lastCoordinate)) {
                $fromPoint = implode(',', $firstCoordinate);
                $toPoint = implode(',', $lastCoordinate);
            } else {
                $fromPoint = '';
                $toPoint = '';
            }
        } else {
            $fromPoint = '';
            $toPoint = '';
        }

        //get the cai huts intersecting with the hiking route
        $huts = json_decode($hikingRoute->cai_huts);
        $caiHuts = [];
        //transform the huts array into an associative array where the key is hut id and value is the hut updated_at
        if (!empty($huts)) {
            foreach ($huts as $hut) {
                $hutModel = CaiHuts::find($hut);
                $updated_at = $hutModel->updated_at;
                $caiHuts[$hut] = $updated_at;
            }
        }

        //get the sections associated with the hiking route
        $sections = $hikingRoute->sections;
        $sectionsIds = $sections->pluck('updated_at', 'id')->toArray();

        // get the abstract from the hiking route and get only it description
        $abstract = $hikingRoute->tdh['abstract']['it'] ?? '';

        //get the difficulty based on cai_scale value
        $difficulty;

        switch ($hikingRoute->cai_scale) {
            case 'T':
                $difficulty = 'Turistico';
                break;
            case 'E':
                $difficulty = 'Escursionistico';
                break;
            case 'EE':
                $difficulty = 'Escursionistico per Esperti';
                break;
            case 'EEA':
                $difficulty = 'Escursionistco per Esperti con Attrezzatura';
                break;
            case 'EEA:F':
                $difficulty = 'Escursionistco per Esperti con Attrezzatura';
                break;
            case 'EEA:D':
                $difficulty = 'Escursionistco per Esperti con Attrezzatura';
                break;
            case 'EEA:MD':
                $difficulty = 'Escursionistco per Esperti con Attrezzatura';
                break;
            case 'EEA:E':
                $difficulty = 'Escursionistco per Esperti con Attrezzatura';
                break;
            default:
                $difficulty = 'Non definito';
        }

        //build the geojson
        $geojson = [];
        $geojson['type'] = 'Feature';

        $properties = [];
        $properties['id'] = $hikingRoute->id;
        $properties['ref'] = $hikingRoute->ref;
        $properties['name'] = $hikingRoute->name ?? '';
        $properties['abstract'] = $abstract;
        $properties['info'] = 'Sezioni del Club Alpino Italiano, Guide Alpine o Guide Ambientali Escursionistiche';
        $properties['activity'] = 'Escursionismo';
        $properties['symbol'] = 'Segnaletica standard CAI';
        $properties['cai_scale'] = $hikingRoute->cai_scale ?? '';
        $properties['from'] = $hikingRoute->from ?? '';
        $properties['to'] = $hikingRoute->to ?? '';
        $properties['from:coordinate'] = $fromPoint;
        $properties['to:coordinate'] = $toPoint;
        $properties['distance'] = $hikingRoute->distance ?? 100;
        $properties['duration_forward'] = $tdh['duration_forward'] ?? '';
        $properties['duration_backward'] = $tdh['duration_backward'] ?? '';
        $properties['ele_max'] = $tdh['ele_max'] ?? '';
        $properties['ele_min'] = $tdh['ele_min'] ?? '';
        $properties['ele_from'] = $tdh['ele_from'] ?? '';
        $properties['ele_to'] = $tdh['ele_to'] ?? '';
        $properties['ascent'] = $tdh['ascent'] ?? '';
        $properties['descent'] = $tdh['descent'] ?? '';
        $properties['difficulty'] = $difficulty;
        $properties['issues_status'] = $hikingRoute->issues_status;
        $properties['section_ids'] = $sectionsIds ?? [];
        $properties['cai_huts'] = $caiHuts;
        $properties['pois'] = count($pois) > 0 ? $pois->pluck('updated_at', 'id')->toArray() : [];
        $properties['map'] = route('hiking-route-public-page', ['id' => $hikingRoute->id]);
        $properties['gpx_url'] = $tdh['gpx_url'] ?? '';
        $properties['images'] = [];

        $geojson['properties'] = $properties;
        $geojson['geometry'] = $geometry;

        //save only when the data is different. Otherwise the hiking route event observer will be triggered to an infinite loop (on save) [App/Models/HikingRoute.php line 100]
        if ($hikingRoute->cached_mitur_api_data != json_encode($geojson)) {
            $hikingRoute->cached_mitur_api_data = json_encode($geojson);
            $hikingRoute->save();
        }

        Log::info("End caching data for hiking route " . $hikingRoute->id);
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
