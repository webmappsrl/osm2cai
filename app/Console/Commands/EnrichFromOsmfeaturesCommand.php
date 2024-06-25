<?php

namespace App\Console\Commands;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Jobs\EnrichFromOsmfeaturesJob;

class EnrichFromOsmfeaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "osm2cai:enrich-from-osmfeatures {osmfeature=places : The feature to retrieve from osmfeatures API.}.";

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
                $model = EcPoi::class;
                break;
            case 'admin-areas':
                $osmfeaturesBaseApi .= 'admin-areas';
                break;
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
        $allModels = $model::all();
        foreach ($allModels as $model) {
            $osmId = $model->osm_id;
            $osmType = $model->osm_type;
            $osmfeaturesApi = $osmfeaturesBaseApi . '/' . $osmType . $osmId;
            Log::info("Enriching $model->name $osmType$osmId");
            try {
                $osmfeaturesData = Http::get($osmfeaturesApi)->json();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $this->info("Response not successful. Skipping $osmType $osmId");
                continue;
            }
            //if there is a message property the feature is not found.
            if (isset($osmfeaturesData['message'])) { //TODO make json message consistent in osmfeatures api (for example: "message": "Not found")
                Log::warning("Not found. Skipping $osmType$osmId");
                $this->info("Not found. Skipping $osmType$osmId");
                continue;
            }
            Log::info("Dispatching job for $osmType$osmId");
            $this->info("Dispatching job for $osmType$osmId");
            EnrichFromOsmfeaturesJob::dispatch($model, $osmfeaturesData);
        }
        Log::info("Enrichment completed for feature $feature");
    }
}
