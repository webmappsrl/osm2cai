<?php

namespace App\Console\Commands;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\EnrichFromOsmfeaturesJob;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class EnrichFromOsmfeaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "osm2cai:enrich-from-osmfeatures {osmfeature=places : The feature to retrieve from osmfeatures API. Available features are: places, poles, admin-areas and hiking-routes.}.";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Enrich data from Osmfeatures API for the provided feature. Available features are: places, poles, admin-areas and hiking-routes.";

    /**
     * The console command usage example.
     *
     * @var string
     */
    protected $usage = 'osm2cai:enrich-from-osmfeatures {osmfeature=places : The feature to retrieve from API}';

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
        $feature = $this->argument('osmfeature');
        Log::info("Starting enrichment for feature $feature");
        $osmfeaturesBaseApi = "https://osmfeatures.maphub.it/api/v1/features/";

        switch ($feature) {
            case 'places':
                $osmfeaturesBaseApi .= 'places';
                $this->enrichPois();
                return 0;
            case 'admin-areas':
                $osmfeaturesBaseApi .= 'admin-areas';
                $this->enrichRegions();
                return 0;
            case 'poles':
                $osmfeaturesBaseApi .= 'poles';
                break;
            case 'hiking-routes':
                $osmfeaturesBaseApi .= 'hiking-routes';
                $model = HikingRoute::class;
                break;
            default:
                $this->error("The provided feature is not available. Available features are: places, poles, admin-areas and hiking-routes.");
                Log::error("The provided feature is not available. Available features are: places, poles, admin-areas and hiking-routes.");
                return 1;
        }
    }

    protected function enrichPois()
    {
        $pois = EcPoi::all();
        foreach ($pois as $poi) {
            $osmId = $poi->osm_id;
            if (is_null($osmId)) {
                $this->info("No osm id for the poi $poi->name. Skipping");
                Log::info("No osm id for the poi $poi->name. Skipping");
                continue;
            }
            $osmType = $poi->osm_type;
            if (is_null($osmType)) {
                $this->info("No osm type for the poi $poi->name. Skipping");
                Log::info("No osm type for the poi $poi->name. Skipping");
                continue;
            }
            $osmfeaturesApi = $osmfeaturesBaseApi . '/' . $osmType . $osmId;
            Log::info("Enriching $poi->name $osmType$osmId");
            try {
                $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $this->info("Response not successful. Skipping $osmType $osmId");
                continue;
            }
            if (!$osmfeaturesData) {
                Log::warning("Response not successful, please check $osmfeaturesApi. Skipping $osmType $osmId");
                $this->info("Response not successful, please check $osmfeaturesApi. Skipping $osmType $osmId");
                continue;
            }

            //if there is a message property the feature is not found.
            if (isset($osmfeaturesData['message'])) { //TODO make json message consistent in osmfeatures api (for example: "message": "Not found")
                Log::warning("Not found $osmfeaturesApi. Skipping");
                $this->info("Not found $osmfeaturesApi. Skipping");
                continue;
            }
            Log::info("Dispatching job for $osmfeaturesApi");
            $this->info("Dispatching job for $osmfeaturesApi");
            EnrichFromOsmfeaturesJob::dispatch($poi, $osmfeaturesData);
        }
        Log::info("Enrichment completed for feature $feature");
    }


    protected function enrichRegions()
    {
        //hardcoded 21 osmfeatures id for regions
        $osmfeaturesIds = [
            'R42004',
            'R53060',
            'R42611',
            'R179296',
            'R39152',
            'R41977',
            'R43648',
            'R44874',
            'R44879',
            'R301482',
            'R53937',
            'R1783980',
            'R41256',
            'R40784',
            'R40218',
            'R40137',
            'R40095',
            'R8654',
            'R7361997',
            'R45757',
            'R45155',
        ];
        foreach ($osmfeaturesIds as $osmfeaturesId) {
            $osmfeaturesApi = "https://osmfeatures.maphub.it/api/v1/features/admin-areas/" . $osmfeaturesId;
            Log::info("Enriching region $osmfeaturesId");
            $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            if ($osmfeaturesData['properties']['name'] == 'Sardigna/Sardegna') {
                $osmfeaturesData['properties']['name'] = 'Sardegna';
            }
            if ($osmfeaturesData['properties']['name'] == 'Trentino-Alto Adige/Südtirol') {
                $osmfeaturesData['properties']['name'] = 'Trentino-Alto Adige';
            }
            if ($osmfeaturesData['properties']['name'] == "Valle d'Aosta / Vallée d'Aoste") {
                $osmfeaturesData['properties']['name'] = "Valle Aosta";
            }
            $osmfeaturesName = strtolower(str_replace(['-', ' '], '', $osmfeaturesData['properties']['name']));
            $regions = Region::all(); //only 20 records
            foreach ($regions as $region) {
                $regionName = strtolower(str_replace(['-', ' '], '', $region->name));
                if ($regionName === $osmfeaturesName) {
                    EnrichFromOsmfeaturesJob::dispatch($region, $osmfeaturesData);
                    break;
                }
            }
        }
    }

    protected function enrichPois()
    {
        $pois = EcPoi::all();
        foreach ($pois as $poi) {
            $osmId = $poi->osm_id;
            if (is_null($osmId)) {
                $this->info("No osm id for the poi $poi->name. Skipping");
                Log::info("No osm id for the poi $poi->name. Skipping");
                continue;
            }
            $osmType = $poi->osm_type;
            if (is_null($osmType)) {
                $this->info("No osm type for the poi $poi->name. Skipping");
                Log::info("No osm type for the poi $poi->name. Skipping");
                continue;
            }
            $osmfeaturesApi = $osmfeaturesBaseApi . '/' . $osmType . $osmId;
            Log::info("Enriching $poi->name $osmType$osmId");
            try {
                $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $this->info("Response not successful. Skipping $osmType $osmId");
                continue;
            }
            if (!$osmfeaturesData) {
                Log::warning("Response not successful, please check $osmfeaturesApi. Skipping $osmType $osmId");
                $this->info("Response not successful, please check $osmfeaturesApi. Skipping $osmType $osmId");
                continue;
            }

            //if there is a message property the feature is not found.
            if (isset($osmfeaturesData['message'])) { //TODO make json message consistent in osmfeatures api (for example: "message": "Not found")
                Log::warning("Not found $osmfeaturesApi. Skipping");
                $this->info("Not found $osmfeaturesApi. Skipping");
                continue;
            }
            Log::info("Dispatching job for $osmfeaturesApi");
            $this->info("Dispatching job for $osmfeaturesApi");
            EnrichFromOsmfeaturesJob::dispatch($poi, $osmfeaturesData);
        }
        Log::info("Enrichment completed for feature $feature");
    }
}