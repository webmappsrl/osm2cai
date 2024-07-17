<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReconcileCaiHutsWithOsmfeatures extends Command
{
    /**
     * @var string
     */
    protected $signature = 'osm2cai:reconcile-cai_huts {id? : CaiHut id}';

    /**
     * @var string
     */
    protected $description = 'This command reconciles cai_huts with OsmFeatures API based on cai hut geometry using the Osmfeatures api /api/v1/features/places/{lon}/{lat}/{distance}. It saves osmfeatures_id in cai_huts table for the nearest place found.';

    /**
     * @var \Illuminate\Log\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $notReconciledHuts = [];
    protected $huts = [];

    public function __construct()
    {
        $this->logger = Log::channel('caiHutsReconciliations');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $id = $this->argument('id');
        $this->huts = $this->getHuts($id);

        foreach ($this->huts as $hut) {
            $this->reconcileHut($hut);
        }

        $this->logReconciliationSummary();
        $this->info('Reconciliation completed.');
        return 0;
    }

    /**
     * @param int|null $id
     * @return \Illuminate\Support\Collection
     */
    private function getHuts(?int $id = null): \Illuminate\Support\Collection
    {
        return $id ? DB::table('cai_huts')->where('id', $id)->get() : DB::table('cai_huts')->get();
    }

    /**
     * @param \stdClass $hut
     * @param int $distance
     * @return void
     */
    private function reconcileHut(\stdClass $hut, int $distance = 1000): void
    {
        $this->logInfo('Reconciling hut ' . $hut->id);

        $coordinates = $this->getCoordinates($hut);
        if (!$coordinates) {
            return;
        }

        $response = $this->fetchOsmFeatures($coordinates, $distance, $hut);
        if ($response === null || $response->failed()) {
            return;
        }

        $places = $response->json();
        if (empty($places)) {
            $this->logWarning($hut, 'API response is empty. Increasing distance.');
            $this->reconcileHut($hut, $distance * 2);
            return;
        }

        $matchingPlace = $this->getMatchingPlace($places);
        if ($matchingPlace) {
            $this->updateHut($hut, $matchingPlace);
        } else {
            $this->logError($hut, 'No matching place found.');
        }
    }

    /**
     * @param \stdClass $hut
     * @return \stdClass|null
     */
    private function getCoordinates(\stdClass $hut): ?\stdClass
    {
        try {
            return DB::table('cai_huts')
                ->select(DB::raw('ST_X(geometry::geometry) AS longitude, ST_Y(geometry::geometry) AS latitude'))
                ->where('id', $hut->id)
                ->first();
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            $this->logError($hut, 'Unable to fetch coordinates.');
            return null;
        }
    }

    /**
     * @param \stdClass $coordinates
     * @param int $distance
     * @return \Illuminate\Http\Client\Response|null
     */
    private function fetchOsmFeatures(\stdClass $coordinates, int $distance): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::get("https://osmfeatures.maphub.it/api/v1/features/places/{$coordinates->longitude}/{$coordinates->latitude}/{$distance}");
        } catch (\Exception $e) {
            $this->logger->warning('API request failed. Error: ' . $e->getMessage());
            $this->logError($hut, 'API request failed.');
            return null;
        }
    }

    /**
     * @param array $places
     * @return array|null
     */
    private function getMatchingPlace(array $places): ?array
    {
        $matchingPlaces = [];
        foreach ($places as $place) {
            if (($place['class'] === 'tourism' && $place['subclass'] === 'huts') ||
                ($place['class'] === 'amenity' && $place['subclass'] === 'shelter')
            ) {
                $matchingPlaces[] = $place;
            }
        }
        //osmfeatures api is ordered by distance so the first result is the closest
        return $matchingPlaces[0] ?? null;
    }

    /**
     * @param \stdClass $hut
     * @param array $matchingPlace
     * @return void
     */
    private function updateHut(\stdClass $hut, array $matchingPlace): void
    {
        DB::table('cai_huts')->where('id', $hut->id)->update([
            'osmfeatures_id' => $matchingPlace['osmfeatures_id']
        ]);
        $this->logInfo('Updated hut ' . $hut->id . ' with OSMFeatures ID ' . $matchingPlace['osmfeatures_id']);
    }

    /**
     * @param \stdClass $hut
     * @param string $message
     * @return void
     */
    private function logError(\stdClass $hut, string $message): void
    {
        $url = url('resource/cai-huts/' . $hut->id);
        $this->logger->error("Reconciliation failed for CAI Hut ID {$hut->id}: {$message}");
        $this->info("Reconciliation failed for CAI Hut ID {$hut->id}: {$message}");
        $this->notReconciledHuts[] = ['url' => $url, 'reason' => $message];
    }

    /**
     * @param \stdClass $hut
     * @param string $message
     * @return void
     */
    private function logWarning(\stdClass $hut, string $message): void
    {
        $this->logger->warning("Warning for CAI Hut ID {$hut->id}: {$message}");
        $this->info("Warning for CAI Hut ID {$hut->id}: {$message}");
    }

    /**
     * @param string $message
     * @return void
     */
    private function logInfo(string $message): void
    {
        $this->logger->info($message);
        $this->info($message);
    }

    /**
     * @return void
     */
    private function logReconciliationSummary(): void
    {
        if (count($this->notReconciledHuts) > 0) {
            $this->logInfo('Reconciliation summary:');
            foreach ($this->notReconciledHuts as $hut) {
                $this->logInfo("Hut URL: {$hut['url']} - Reason: {$hut['reason']}");
            }
        }
    }
}