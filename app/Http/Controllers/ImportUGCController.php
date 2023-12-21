<?php

namespace App\Http\Controllers;

use App\Models\UgcPoi;
use App\Models\UgcMedia;
use App\Models\UgcTrack;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportUGCController extends Controller
{
    public function importUGCFromGeohub()
    {
        try {
            $endPoints = [
                'poi' => 'https://geohub.webmapp.it/api/ugc/poi/geojson/it.webmapp.osm2cai/list',
                'track' => 'https://geohub.webmapp.it/api/ugc/track/geojson/it.webmapp.osm2cai/list',
                'media' => 'https://geohub.webmapp.it/api/ugc/media/geojson/it.webmapp.osm2cai/list'
            ];

            $createdElements = [
                'poi' => 0,
                'track' => 0,
                'media' => 0
            ];

            $updatedElements = [];

            foreach ($endPoints as $type => $endPoint) {
                Log::info("Starting sync for $type from $endPoint");
                $list = json_decode($this->get_content($endPoint), true);

                foreach ($list as $id => $updated_at) {
                    $model = $this->getModel($type, $id);
                    Log::info("Syncing {$type} with id {$id}");

                    $geoJson = $this->getGeojson("https://geohub.webmapp.it/api/ugc/{$type}/geojson/{$id}/osm2cai");

                    if ($model->wasRecentlyCreated) {
                        $createdElements[$type]++;
                        Log::info("{$type} with id {$id} created");
                    }

                    if ($model->updated_at < $updated_at || $model->wasRecentlyCreated) {
                        $this->syncRecord($model, $geoJson, $id);
                        if ($model->updated_at < $updated_at) {
                            $updatedElements[] = ucfirst($type) . ' with id ' . $id . ' updated';
                            Log::info("{$type} with id {$id} updated");
                        }
                    }
                }
            }

            Log::info('Import process completed. Created elements: ' . json_encode($createdElements) . ', Updated elements: ' . json_encode($updatedElements));

            return view('importedUgc', array_merge($createdElements, ['updatedElements' => $updatedElements]));
        } catch (\Exception $e) {
            Log::error('Error occurred during import process: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during the import process. Please try again later.'], 500);
        }
    }

    private function getModel($type, $id)
    {
        $model = 'App\Models\Ugc' . ucfirst($type);
        return $model::firstOrCreate(['geohub_id' => $id]);
    }

    private function getGeojson($url)
    {
        $geoJson = json_decode($this->get_content($url), true);
        if ($geoJson === null) {
            throw new \Exception('Failed to retrieve GeoJSON data');
        }
        return $geoJson;
    }

    private function syncRecord($model, $geoJson, $id)
    {
        $user = User::where('email', $geoJson['properties']['user_email'])->first();
        if ($user === null) {
            Log::channel('missingUsers')->info('User with email ' . $geoJson['properties']['user_email'] . ' not found');
            throw new \Exception('User not found');
        }

        $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($geoJson['geometry']) . '\')');
        $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');

        $data = [
            'name' => $geoJson['properties']['name'],
            'geometry' => $geometry,
            'raw_data' => $geoJson['properties']['raw_data'],
            'updated_at' => $geoJson['properties']['updated_at'],
            'taxonomy_wheres' => $geoJson['properties']['taxonomy_wheres'],
        ];

        if ($model instanceof UgcMedia) {
            $data['relative_url'] = $geoJson['url'];
            $poisIds = $geoJson['properties']['ugc_pois'];
            $tracksIds = $geoJson['properties']['ugc_tracks'];
            UgcPoi::whereIn('geohub_id', $poisIds)->pluck('id')->toArray();
            UgcTrack::whereIn('geohub_id', $tracksIds)->pluck('id')->toArray();
            $model->ugcPois()->sync($poisIds);
            $model->ugcTracks()->sync($tracksIds);
        }

        $model->updateOrCreate(['geohub_id' => $id], $data);
    }

    private function get_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        $data = curl_exec($ch);
        if ($data === false) {
            throw new \Exception('Failed to retrieve content from URL');
        }
        curl_close($ch);
        return $data;
    }
}
