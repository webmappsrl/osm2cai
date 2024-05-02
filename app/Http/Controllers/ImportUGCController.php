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

            //get the ugc pois with form_id = null
            $ugcPois = UgcPoi::whereNull('form_id')->get();

            if ($ugcPois->count() > 0) {
                foreach ($ugcPois as $ugcPoi) {
                    $this->fillNullFormId($ugcPoi);
                }
            }

            Log::info('Import process completed. Created elements: ' . json_encode($createdElements) . ', Updated elements: ' . json_encode($updatedElements));

            return view('importedUgc', array_merge($createdElements, ['updatedElements' => $updatedElements]));
        } catch (\Exception $e) {
            Log::error('Error occurred during import process: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in file ' . $e->getFile());
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

        if ($geoJson['geometry']) {
            $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($geoJson['geometry']) . '\')');
            $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
        } else {
            $geometry = null;
        }

        $data = [
            'name' => $geoJson['properties']['name'] ?? null,
            'geometry' => $geometry,
            'raw_data' => $geoJson['properties']['raw_data'] ?? null,
            'updated_at' => $geoJson['properties']['updated_at'] ?? null,
            'taxonomy_wheres' => $geoJson['properties']['taxonomy_wheres'] ?? null,
        ];

        if ($user != null) {
            $data['user_id'] = $user->id;
        }

        //if the model is a poi get the id from the raw_data and fill the form_id column with the value
        if ($model instanceof UgcPoi) {
            $rawData = json_decode($geoJson['properties']['raw_data'], true);
            $data['form_id'] = $rawData['id'] ?? null;
            if ($user == null) {
                Log::channel('missingUsers')->info('User with email ' . $geoJson['properties']['user_email'] . ' not found');
                $data['user_no_match'] = $geoJson['properties']['user_email'];
            }
        }

        if ($model instanceof UgcMedia) {
            $data['relative_url'] = $geoJson['properties']['url'] ?? null;
            $poisGeohubIds = $geoJson['properties']['ugc_pois'] ?? [];
            $tracksGeohubIds = $geoJson['properties']['ugc_tracks'] ?? [];

            if (count($poisGeohubIds) > 0) {
                $poisIds = UgcPoi::whereIn('geohub_id', $poisGeohubIds)->pluck('id')->toArray();
                $model->ugc_pois()->sync($poisIds);
            }
            if (count($tracksGeohubIds) > 0) {
                $tracksIds = UgcTrack::whereIn('geohub_id', $tracksGeohubIds)->pluck('id')->toArray();
                $model->ugc_tracks()->sync($tracksIds);
            }
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
            if (curl_errno($ch) == CURLE_OPERATION_TIMEDOUT) {
                throw new \Exception('Timeout occurred when retrieving content from URL: ' . $url);
            }
            throw new \Exception('Failed to retrieve content from URL');
        }
        curl_close($ch);
        return $data;
    }

    private function fillNullFormId(UgcPoi $ugcPoi)
    {
        //get the raw_data
        $rawData = json_decode($ugcPoi->raw_data, true);
        //if the raw_data is not null
        if ($rawData) {
            //get the id from the raw_data
            $formId = $rawData['id'];
            //update the form_id of the ugc poi
            $ugcPoi->update(['form_id' => $formId]);
        }
    }
}
