<?php

namespace App\Console\Commands;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\EnrichFromOsmfeaturesJob;
use App\Models\CaiHuts;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class EnrichFromOsmfeaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "osm2cai:enrich-from-osmfeatures {osmfeature=places : The feature to retrieve from osmfeatures API. Available features are: places, poles, admin-areas and hiking-routes.} {--score=0  : The score to filter the data to be enriched.}.";

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
        $this->logInfo("Starting enrichment for feature $feature");
        $osmfeaturesBaseApi = "https://osmfeatures.maphub.it/api/v1/features/";
        $notFound = [];

        switch ($feature) {
            case 'places':
                $osmfeaturesBaseApi .= 'places';
                $this->enrichPois($osmfeaturesBaseApi, $notFound);
                break;
            case 'admin-areas':
                $osmfeaturesBaseApi .= 'admin-areas';
                $this->enrichRegions($osmfeaturesBaseApi, $notFound);
                break;
            case 'poles':
                $osmfeaturesBaseApi .= 'poles';
                break;
            case 'hiking-routes':
                $osmfeaturesBaseApi .= 'hiking-routes';
                $model = HikingRoute::class;
                break;
            case 'cai-huts':
                $osmfeaturesBaseApi .= 'places';
                $this->enrichCaiHuts($osmfeaturesBaseApi, $notFound);
                break;
            default:
                $this->logError("The provided feature is not available. Available features are: places, poles, admin-areas and hiking-routes.");
                return 1;
        }

        if (!empty($notFound)) {
            $this->logWarning('The following POIs were not found or could not be enriched: ' . implode(', ', $notFound));
        }

        return 0;
    }

    protected function enrichRegions($url, &$notFound)
    {
        // Hardcoded 21 osmfeatures id for regions
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

        $regions = Region::all(); // Only 20 records

        foreach ($osmfeaturesIds as $osmfeaturesId) {
            $osmfeaturesApi = $url . '/' . $osmfeaturesId;
            $this->logInfo("Enriching region $osmfeaturesId");
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
            $found = false;

            foreach ($regions as $region) {
                $regionName = strtolower(str_replace(['-', ' '], '', $region->name));
                if ($regionName === $osmfeaturesName) {
                    EnrichFromOsmfeaturesJob::dispatch($region, $osmfeaturesData);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $notFound[] = $osmfeaturesId;
            }
        }
    }

    protected function enrichPois(string $url, &$notFound)
    {
        $notFoundCounter = 0;
        $notFoundLogFile = storage_path('logs/osmfeatures_not_found.log');

        $pois = $this->option('score') ? EcPoi::where('score', '>', $this->option('score'))->get() : EcPoi::all();
        $totalPois = $pois->count();
        $processedCounter = 0;

        foreach ($pois as $poi) {
            $processedCounter++;
            $osmId = $poi->osm_id;
            if (is_null($osmId)) {
                $this->logInfo("No osm id for the poi $poi->name with id $poi->id. Skipping");
                $notFoundCounter++;
                $notFound[] = $poi->name . ' (ID: ' . $poi->id . ')';
                continue;
            }
            $osmType = $poi->osm_type;
            if (is_null($osmType)) {
                $this->logInfo("No osm type for the poi $poi->name with id $poi->id. Skipping");
                $notFoundCounter++;
                $notFound[] = $poi->name . ' (ID: ' . $poi->id . ')';
                continue;
            }
            $osmfeaturesApi = $url . '/' . $osmType . $osmId;
            $this->logInfo("Enriching $poi->name $osmType$osmId");
            try {
                $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            } catch (\Exception $e) {
                $this->logError($e->getMessage());
                $this->logInfo("Response not successful. Skipping $poi->osmfeatures_id");
                $notFoundCounter++;
                $notFound[] = $poi->name . ' (ID: ' . $poi->id . ')';
                continue;
            }
            if (!$osmfeaturesData) {
                $this->logWarning("Response not successful, please check $osmfeaturesApi. Skipping $osmType $osmId");
                $notFoundCounter++;
                $notFound[] = $poi->name . ' (ID: ' . $poi->id . ')';
                continue;
            }

            // If there is a message property the feature is not found.
            if (isset($osmfeaturesData['message'])) { // TODO: make json message consistent in osmfeatures api (for example: "message": "Not found")
                $this->logWarning("Not found $osmfeaturesApi. Skipping");
                $notFoundCounter++;
                $notFound[] = $poi->name . ' (ID: ' . $poi->id . ')';
                continue;
            }
            $this->logInfo("Dispatching job for $osmfeaturesApi");
            EnrichFromOsmfeaturesJob::dispatch($poi, $osmfeaturesData);

            // Update counter and print to terminal
            $this->logInfo("Processed $processedCounter/$totalPois");
        }

        $this->logWarning("$notFoundCounter not found");
        file_put_contents($notFoundLogFile, implode("\n", $notFound), FILE_APPEND);
    }


    protected function enrichCaiHuts(string $url, &$notFound)
    {
        //get only caihuts with osmfeatures_id (reconciliated) to make a get request to osmfeatures api
        $caiHuts = CaiHuts::where('osmfeatures_id', '!=', null)->get();
        foreach ($caiHuts as $caiHut) {
            $osmfeaturesId = $caiHut->osmfeatures_id;

            $osmfeaturesApi = $url . '/' . $osmfeaturesId;
            $this->logInfo("Enriching $caiHut->name $osmfeaturesId;");
            try {
                $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            } catch (\Exception $e) {
                $this->logError($e->getMessage());
                $this->logInfo("Response not successful. Skipping $caiHut->osmfeatures_id");
                $notFound[] = $caiHut->name . ' (ID: ' . $caiHut->id . ')';
                continue;
            }
            if (!$osmfeaturesData) {
                $this->logWarning("Response not successful, please check $osmfeaturesApi. Skipping $osmfeaturesId");
                $notFound[] = $caiHut->name . ' (ID: ' . $caiHut->id . ')';
                continue;
            }
            $this->logInfo("Dispatching job for $osmfeaturesApi");
            EnrichFromOsmfeaturesJob::dispatch($caiHut, $osmfeaturesData);
        }
    }

    /**
     * Log and print information.
     *
     * @param string $message
     * @return void
     */
    protected function logInfo(string $message)
    {
        Log::info($message);
        $this->info($message);
    }

    /**
     * Log and print warnings.
     *
     * @param string $message
     * @return void
     */
    protected function logWarning(string $message)
    {
        Log::warning($message);
        $this->warn($message);
    }

    /**
     * Log and print errors.
     *
     * @param string $message
     * @return void
     */
    protected function logError(string $message)
    {
        Log::error($message);
        $this->error($message);
    }
}