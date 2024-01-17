<?php

namespace App\Nova\Actions;

use App\Models\EcPoi;
use App\Models\HikingRoute;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Laravel\Nova\Fields\ActionFields;
use Symm\Gisconverter\Geometry\Point;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ImportPois extends Action
{
    use InteractsWithQueue, Queueable;

    public $model;

    function __construct($model = null)
    {

        $this->model = $model;

        if (!is_null($resourceId = request('resourceId'))) {
            $this->model = HikingRoute::find($resourceId);
        }
    }

    public $name = "IMPORT POIS";

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $osm_ids = str_replace(' ', '', $fields->osm_ids);
        $osm_ids = explode(',', $osm_ids);
        $osm_ids = array_unique($osm_ids);
        $endpoints = count($osm_ids);
        $counter = 0;

        foreach ($osm_ids as $osmId) {
            $typeAndId = explode('/', $osmId);
            $type = $typeAndId[0];
            $id = $typeAndId[1];
            $baseUrl = "https://api.openstreetmap.org/api/0.6/$type/$id";
            $urlTail = $type === 'node' ? ".json" : "/full.json";
            $url = $baseUrl . $urlTail;
            $abort = Action::danger("$type con ID $id non trovato. Per favore verifica l'ID e riprova.");

            try {
                $response = Http::get($url);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                if ($e->response->status() == 410) {
                    Log::info("OSM ID $osmId not found");
                    return $abort;
                } else if ($e->response->status() == 404) {
                    Log::info("OSM ID $osmId not found");
                    return $abort;
                } else {
                    throw $e;
                }
            }
            $data = $response->json();
            if ($data === null) {
                Log::info("OSM ID $osmId not found");
                return $abort;
            }
            $elements = $data['elements'];
            if ($type !== 'node') {

                $coordinates = [];
                //loop over all the elements, take lat and long and calculate the centroid
                foreach ($elements as $element) {
                    if ($element['type'] === 'node') {
                        $coordinates[] = [$element['lon'], $element['lat']];
                    } else {
                        $poi = EcPoi::updateOrCreate(['osm_id' => $element['id']], [
                            'name' => $element['name'] ?? 'no name (' . $element['id'] . ')',
                            'description' => $element['tags']['description'] ?? null,
                            'geometry' => null,
                            'tags' => isset($element['tags']) ? json_encode($element['tags']) : null,
                        ]);
                        $counter++;
                    }
                }
                //get the centroid using the coordinates array
                $centroid = new Point($this->calculateCentroid($coordinates));
                $poi->geometry = $centroid->toWKT();
                $poi->save();
                if ($counter === $endpoints)
                    return Action::message('Import completato');
            } else {
                foreach ($elements as $element) {
                    if ($element['type'] !== 'node') {
                        continue;
                    }
                    $this->importPoi($element);
                }
            }
        }
        return Action::message('Import completato');
    }

    private function importPoi($data)
    {
        $type = $data['type'];
        $osmId = $data['id'];
        $name = $data['name'] ?? 'no name (' . $data['id'] . ')';
        $description = $data['tags']['description'] ?? null;
        $geometry = $this->getGeometry($data, $type);
        $tags = isset($data['tags']) ? json_encode($data['tags']) : null;


        $poi = EcPoi::updateOrCreate(['osm_id' => $osmId], [
            'name' => $name,
            'description' => $description,
            'geometry' => $geometry,
            'tags' => $tags,
        ]);
    }

    private function getGeometry($data, $type)
    {
        if ($type === 'node') {
            $point =  new Point([$data['lon'], $data['lat']]);
            return $point->toWKT();
        }
    }

    private function calculateCentroid($coordinates)
    {
        $sumLat = 0;
        $sumLon = 0;
        $count = count($coordinates);
        foreach ($coordinates as $coordinate) {
            $sumLon += $coordinate[0];
            $sumLat += $coordinate[1];
        }

        $centroidLon = $sumLon / $count;
        $centroidLat = $sumLat / $count;

        $centroid = [$centroidLon, $centroidLat];

        return $centroid;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Textarea::make('OSM IDs', 'osm_ids')->help('Inserisci gli ID OSM separati da virgola. Esempio: node/123456,way/123456,relation/123456')
        ];
    }
}
