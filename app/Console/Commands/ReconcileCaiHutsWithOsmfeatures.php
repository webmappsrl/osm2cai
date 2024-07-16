<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReconcileCaiHutsWithOsmfeatures extends Command
{
    protected $signature = 'osm2cai:reconcile-cai_huts {id?}';
    protected $description = 'This command reconciles cai_huts with OsmFeatures API based on cai hut geometry using the Osmfeatures api /api/v1/features/places/{lon}/{lat}/{distance}. It saves osmfeatures_id in cai_huts table fo the nearest place found.';

    protected $logger;


    public function __construct()
    {
        $this->logger = Log::channel('caiHutsReconciliations');
        parent::__construct();
    }
    public function handle()
    {

        $id = $this->argument('id');
        $all = $this->argument('id') ? false : true;

        if ($all) {
            $huts = DB::table('cai_huts')->get();
        } else {
            $huts = DB::table('cai_huts')->where('id', $id)->get();
        }

        foreach ($huts as $hut) {
            $this->reconcileHut($hut);
        }

        $this->info('Reconciliation completed.');
        $this->logger->info('Reconciliation completed.');
        return 0;
    }

    private function reconcileHut($hut, int $distance = 1000)
    {

        //get coordinates for the hut
        try {
            $coordinates = DB::table('cai_huts')
                ->select(DB::raw('ST_X(geom::geometry) AS longitude, ST_Y(geom::geometry) AS latitude'))
                ->where('id', $hut->id)
                ->first();
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            $this->info('Failed to fetch coordinates for hut ' . $hut->id);
            return;
        }

        if (!$coordinates) {
            $this->logger->warning('No coordinates found for hut ' . $hut->id);
            $this->info('No coordinates found for hut ' . $hut->id);
            return;
        }

        //http request to osmfeatures api
        try {
            $response = Http::get('https://osmfeatures.maphub.it/api/v1/features/places/' . $coordinates->longitude . '/' . $coordinates->latitude . '/' . $distance);
        } catch (\Exception $e) {
            $this->logger->warning('API request failed for hut ' . $hut->id . '. Error: ' . $e->getMessage());
            $this->info('API request failed for hut ' . $hut->id . '. Error: ' . $e->getMessage());
            return;
        }

        if ($response->failed()) {
            $this->logger->warning('API request failed for hut ' . $hut->id);
            $this->info('API request failed for hut ' . $hut->id);
            return;
        }

        //if the response is empty try to increase distance
        if ($response->json() === []) {
            $this->logger->warning('API response is empty for hut ' . $hut->id . '. increasing distance');
            $this->info('API response is empty for hut ' . $hut->id . '. increasing distance');
            $distance = $distance * 2;
            $this->reconcileHut($hut, $distance);
            return;
        }

        $places = $response->json();

        $matchingPlaces = [];

        //filter by class and subclass
        $this->logger->info('filtering places by class (tourism/amenity) and subclass(huts/shelter)');
        $this->info('filtering places by class (tourism/amenity) and subclass(huts/shelter)');
        foreach ($places as $place) {
            if (
                ($place['class'] === 'tourism' && $place['subclass'] === 'huts') ||
                ($place['class'] === 'amenity' && $place['subclass'] === 'shelter')
            ) {
                $matchingPlaces[] = $place;
            }
        }
        $this->logger->info('Found ' . count($matchingPlaces) . ' matching places for hut ' . $hut->id);
        $this->info('Found ' . count($matchingPlaces) . ' matching places for hut ' . $hut->id);

        //get the nearest place
        if (count($matchingPlaces) > 0) {
            $this->logger->info('getting nearest place...');
            $this->info('getting nearest place...');
            $smallestDistance = PHP_INT_MAX;
            $matchingPlace = null;
            foreach ($matchingPlaces as $place) {
                if ($place['distance'] < $smallestDistance) {
                    $smallestDistance = $place['distance'];
                    $matchingPlace = $place;
                }
            }

            $this->logger->info('nearest place: ' . $matchingPlace['osmfeatures_id']);
            $this->info('nearest place: ' . $matchingPlace['osmfeatures_id']);

            if ($matchingPlace) {
                DB::table('cai_huts')->where('id', $hut->id)->update([
                    'osmfeatures_id' => $matchingPlace['osmfeatures_id']
                ]);
            } else {
                $this->logger->warning('No matching place found for hut ' . $hut->id);
            }
        }
    }
}
