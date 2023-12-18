<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Text;
use App\Http\Facades\OsmClient;
use App\Models\Itinerary;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportItinerary extends Action
{
    use InteractsWithQueue, Queueable;

    public function handle(ActionFields $fields, Collection $models)
    {
        if ($fields['ids']) {
            $ids = explode(',', $fields->ids);
        } else {
            return Action::danger('No IDs provided.');
        }

        if ($fields->import_source === 'OSM') {
            foreach ($ids as $id) {
                $successCount = 0;
                $errorCount = [];
                try {
                    $id = trim($id);
                    $osmClient = new OsmClient();
                    $geojson_content = $osmClient::getGeojson('relation/' . $id);
                    $geojson_content = json_decode($geojson_content, true);
                    if (empty($geojson_content['geometry']) || empty($geojson_content['properties'])) {
                        throw new Exception('Wrong OSM ID');
                    }
                    $geojson_geometry = json_encode($geojson_content['geometry']);
                    $geometry = DB::select("SELECT ST_AsText(ST_Force3D(ST_LineMerge(ST_GeomFromGeoJSON('" . $geojson_geometry . "')))) As wkt")[0]->wkt;

                    $name_array = array();

                    if (array_key_exists('ref', $geojson_content['properties']) && !empty($geojson_content['properties']['ref'])) {
                        array_push($name_array, $geojson_content['properties']['ref']);
                    }
                    if (array_key_exists('name', $geojson_content['properties']) && !empty($geojson_content['properties']['name'])) {
                        array_push($name_array, $geojson_content['properties']['name']);
                    }
                    $itineraryName = !empty($name_array) ? implode(' - ', $name_array) : null;
                    $itineraryName = str_replace('"', '', $itineraryName);

                    $itinerary = Itinerary::updateOrCreate(
                        [
                            'osm_id' => intval($id),
                        ],
                        [
                            'name' => $itineraryName,
                            'geometry' => $geometry,
                        ]
                    );

                    $itinerary->osm_id = intval($id);
                    $itinerary->ref = $geojson_content['properties']['ref'];

                    // //check if ascent, descent, distance duration_forward and duration_backward are not null in the geojson data and if so, update the $itinerary
                    // $itinerary->cai_scale = (key_exists('cai_scale', $geojson_content['properties']) && $geojson_content['properties']['cai_scale']) ? $geojson_content['properties']['cai_scale'] : $itinerary->cai_scale;
                    // $itinerary->from = (key_exists('from', $geojson_content['properties']) && $geojson_content['properties']['from']) ? $geojson_content['properties']['from'] : $itinerary->from;
                    // $itinerary->to = (key_exists('to', $geojson_content['properties']) && $geojson_content['properties']['to']) ? $geojson_content['properties']['to'] : $itinerary->to;
                    // $itinerary->ascent = (key_exists('ascent', $geojson_content['properties']) && $geojson_content['properties']['ascent']) ? $geojson_content['properties']['ascent'] : $itinerary->ascent;
                    // $itinerary->descent = (key_exists('descent', $geojson_content['properties']) && $geojson_content['properties']['descent']) ? $geojson_content['properties']['descent'] : $itinerary->descent;
                    // $itinerary->distance = (key_exists('distance', $geojson_content['properties']) && $geojson_content['properties']['distance']) ? str_replace(',', '.', $geojson_content['properties']['distance']) : $itinerary->distance;
                    // //duration forward must be converted to minutes
                    // if (key_exists('duration:forward', $geojson_content['properties']) && $geojson_content['properties']['duration:forward'] != null) {
                    //     $duration_forward = str_replace('.', ':', $geojson_content['properties']['duration:forward']);
                    //     $duration_forward = str_replace(',', ':', $duration_forward);
                    //     $duration_forward = str_replace(';', ':', $duration_forward);
                    //     $duration_forward = explode(':', $duration_forward);
                    //     $itinerary->duration_forward = ($duration_forward[0] * 60) + $duration_forward[1];
                    // }
                    // //same for duration_backward
                    // if (key_exists('duration:backward', $geojson_content['properties']) && $geojson_content['properties']['duration:backward'] != null) {
                    //     $duration_backward = str_replace('.', ':', $geojson_content['properties']['duration:backward']);
                    //     $duration_backward = str_replace(',', ':', $duration_backward);
                    //     $duration_backward = str_replace(';', ':', $duration_backward);
                    //     $duration_backward = explode(':', $duration_backward);
                    //     $itinerary->duration_backward = ($duration_backward[0] * 60) + $duration_backward[1];
                    // }
                    // $itinerary->skip_geomixer_tech = true;
                    $itinerary->save();

                    $successCount++;
                } catch (\Exception $e) {
                    array_push($errorCount, $id);
                }
            }

            $message = 'Processed ' . $successCount . ' OSM IDs successfully';
            if (!empty($errorCount)) {
                $message .= ', but encountered errors for ' . implode(", ", $errorCount);
            }

            return Action::message($message);
        } elseif ($fields->import_source === 'OSM2CAI') {
            foreach ($ids as $id) {
                $successCount = 0;
                $errorCount = [];
                try {
                    $id = trim($id);
                    $itinerary = Itinerary::find($id);
                    if (!$itinerary) {
                        return Action::danger('Itinerary with ID ' . $id . ' not found.');
                    }
                    $osmid = $itinerary->osm_id;
                    $osmClient = new OsmClient();
                    $geojson_content = $osmClient::getGeojson('relation/' . $osmid);
                    $geojson_content = json_decode($geojson_content, true);
                    if (empty($geojson_content['geometry']) || empty($geojson_content['properties'])) {
                        throw new Exception('Wrong OSM ID');
                    }
                    $geojson_geometry = json_encode($geojson_content['geometry']);
                    $geometry = DB::select("SELECT ST_AsText(ST_Force3D(ST_LineMerge(ST_GeomFromGeoJSON('" . $geojson_geometry . "')))) As wkt")[0]->wkt;

                    $name_array = array();

                    if (array_key_exists('ref', $geojson_content['properties']) && !empty($geojson_content['properties']['ref'])) {
                        array_push($name_array, $geojson_content['properties']['ref']);
                    }
                    if (array_key_exists('name', $geojson_content['properties']) && !empty($geojson_content['properties']['name'])) {
                        array_push($name_array, $geojson_content['properties']['name']);
                    }
                    $itineraryName = !empty($name_array) ? implode(' - ', $name_array) : null;
                    $itineraryName = str_replace('"', '', $itineraryName);

                    $itinerary = Itinerary::updateOrCreate(
                        [
                            'osm_id' => intval($osmid),
                        ],
                        [
                            'name' => $itineraryName,
                            'geometry' => $geometry,
                        ]
                    );

                    $itinerary->ref = $geojson_content['properties']['ref'];

                    // //check if ascent, descent, distance duration_forward and duration_backward are not null in the geojson data and if so, update the $itinerary
                    // $itinerary->cai_scale = (key_exists('cai_scale', $geojson_content['properties']) && $geojson_content['properties']['cai_scale']) ? $geojson_content['properties']['cai_scale'] : $itinerary->cai_scale;
                    // $itinerary->from = (key_exists('from', $geojson_content['properties']) && $geojson_content['properties']['from']) ? $geojson_content['properties']['from'] : $itinerary->from;
                    // $itinerary->to = (key_exists('to', $geojson_content['properties']) && $geojson_content['properties']['to']) ? $geojson_content['properties']['to'] : $itinerary->to;
                    // $itinerary->ascent = (key_exists('ascent', $geojson_content['properties']) && $geojson_content['properties']['ascent']) ? $geojson_content['properties']['ascent'] : $itinerary->ascent;
                    // $itinerary->descent = (key_exists('descent', $geojson_content['properties']) && $geojson_content['properties']['descent']) ? $geojson_content['properties']['descent'] : $itinerary->descent;
                    // $itinerary->distance = (key_exists('distance', $geojson_content['properties']) && $geojson_content['properties']['distance']) ? str_replace(',', '.', $geojson_content['properties']['distance']) : $itinerary->distance;
                    // //duration forward must be converted to minutes
                    // if (key_exists('duration:forward', $geojson_content['properties']) && $geojson_content['properties']['duration:forward'] != null) {
                    //     $duration_forward = str_replace('.', ':', $geojson_content['properties']['duration:forward']);
                    //     $duration_forward = str_replace(',', ':', $duration_forward);
                    //     $duration_forward = str_replace(';', ':', $duration_forward);
                    //     $duration_forward = explode(':', $duration_forward);
                    //     $itinerary->duration_forward = ($duration_forward[0] * 60) + $duration_forward[1];
                    // }
                    // //same for duration_backward
                    // if (key_exists('duration:backward', $geojson_content['properties']) && $geojson_content['properties']['duration:backward'] != null) {
                    //     $duration_backward = str_replace('.', ':', $geojson_content['properties']['duration:backward']);
                    //     $duration_backward = str_replace(',', ':', $duration_backward);
                    //     $duration_backward = str_replace(';', ':', $duration_backward);
                    //     $duration_backward = explode(':', $duration_backward);
                    //     $itinerary->duration_backward = ($duration_backward[0] * 60) + $duration_backward[1];
                    // }
                    // $itinerary->skip_geomixer_tech = true;
                    $itinerary->save();

                    $successCount++;
                } catch (\Exception $e) {
                    array_push($errorCount, $id);
                }
            }

            $message = 'Processed ' . $successCount . ' OSM2CAI IDs successfully';
            if (!empty($errorCount)) {
                $message .= ', but encountered errors for ' . implode(", ", $errorCount);
            }

            return Action::message($message);
        }
    }

    public function fields()
    {
        return [
            Select::make('Import Source', 'import_source')->options([
                'OSM' => 'OpenStreetMap',
                'OSM2CAI' => 'OSM2CAI',
            ])->rules('required'),

            Text::make('IDs', 'ids')->rules('required')->help('Comma separated IDs e.g. 123,456,789')
        ];
    }
}
