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
        $geohubPoiListEndPoint = 'https://geohub.webmapp.it/api/ugc/poi/geojson/it.webmapp.osm2cai/list';
        $geohubTracklistEndpoint = 'https://geohub.webmapp.it/api/ugc/track/geojson/it.webmapp.osm2cai/list';
        $geohubMediaListEndPoint = 'https://geohub.webmapp.it/api/ugc/media/geojson/it.webmapp.osm2cai/list';

        $geohubPoiList = json_decode(file_get_contents($geohubPoiListEndPoint), true);
        $geohubTrackist = json_decode(file_get_contents($geohubTracklistEndpoint), true);
        $geohubMediaList = json_decode(file_get_contents($geohubMediaListEndPoint), true);

        $poiCreatedElements = 0;
        $trackCreatedElements = 0;
        $mediaCreatedElements = 0;
        $updatedElements = [];

        foreach ($geohubPoiList as $id => $updated_at) {
            try {
                $osm2caiPoi = UgcPoi::firstOrCreate(['geohub_id' => $id]);
                $poiGeoJson = $this->getGeojson('https://geohub.webmapp.it/api/ugc/poi/geojson/' . $id . '/osm2cai');
                if ($osm2caiPoi->wasRecentlyCreated) {
                    $poiCreatedElements++;
                    $user = User::whereEmail($poiGeoJson['properties']['user_email'])->first();
                    $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($poiGeoJson['geometry']) . '\')');
                    $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                    $data = [
                        'name' => $poiGeoJson['properties']['name'],
                        'geometry' => $geometry,
                        'raw_data' => $poiGeoJson['properties']['raw_data'],
                    ];
                    if ($user != null) {
                        $data['user_id'] = $user->id;
                    } else {
                        Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                    }
                    $osm2caiPoi->updateOrCreate(['geohub_id' => $id], $data);
                }
                if ($osm2caiPoi->updated_at < $updated_at) {
                    $user = User::whereEmail($poiGeoJson['properties']['user_email'])->first();
                    $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($poiGeoJson['geometry']) . '\')');
                    $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                    $data = [
                        'name' => $poiGeoJson['properties']['name'],
                        'geometry' => $geometry,
                        'raw_data' => $poiGeoJson['properties']['raw_data'],
                        'updated_at' => $poiGeoJson['properties']['updated_at'],
                    ];
                    if ($user != null) {
                        $data['user_id'] = $user->id;
                    } else {
                        Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                    }
                    $osm2caiPoi->updateOrCreate(['geohub_id' => $id], $data);
                    $updatedElements[] = 'Poi with id ' . $id . ' updated';
                }
            } catch (\Exception $e) {
                Log::debug('Error importing poi with id ' . $id . ': ' . $e->getMessage());
            }
        }
        foreach ($geohubTrackist as $id => $updated_at) {
            $osm2caiTrack = UgcTrack::firstOrCreate(['geohub_id' => $id]);
            $trackGeojson = $this->getGeojson('https://geohub.webmapp.it/api/ugc/track/geojson/' . $id . '/osm2cai');
            if ($osm2caiTrack->wasRecentlyCreated) {
                $trackCreatedElements++;
                $user = User::whereEmail($trackGeojson['properties']['user_email'])->first();
                $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($trackGeojson['geometry']) . '\')');
                $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                $data = [
                    'name' => $trackGeojson['properties']['name'],
                    'geometry' => $geometry,
                    'raw_data' => $trackGeojson['properties']['raw_data'],
                ];

                if ($user != null) {
                    $data['user_id'] = $user->id;
                } else {
                    Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                }
                $osm2caiTrack->updateOrCreate(['geohub_id' => $id], $data);
            }
            if ($osm2caiTrack->updated_at < $updated_at) {
                $user = User::whereEmail($trackGeojson['properties']['user_email'])->first();
                $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($trackGeojson['geometry']) . '\')');
                $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                $data = [
                    'name' => $trackGeojson['properties']['name'],
                    'geometry' => $geometry,
                    'raw_data' => $trackGeojson['properties']['raw_data'],
                    'updated_at' => $trackGeojson['properties']['updated_at'],
                ];
                if ($user != null) {
                    $data['user_id'] = $user->id;
                } else {
                    Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                }
                $osm2caiTrack->updateOrCreate(['geohub_id' => $id], $data);

                $updatedElements[] = 'Track with id ' . $id . ' updated';
            }
        }
        foreach ($geohubMediaList as $id => $updated_at) {
            $osm2caiMedia = UgcMedia::firstOrCreate(['geohub_id' => $id]);
            $mediaGeojson = $this->getGeojson('https://geohub.webmapp.it/api/ugc/media/geojson/' . $id . '/osm2cai');
            if ($osm2caiMedia->wasRecentlyCreated) {
                $mediaCreatedElements++;
                $user = User::whereEmail($mediaGeojson['properties']['user_email'])->first();
                $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($mediaGeojson['geometry']) . '\')');
                $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                $data = [
                    'name' => $mediaGeojson['properties']['name'],
                    'geometry' => $geometry,
                    'raw_data' => $mediaGeojson['properties']['raw_data'],
                ];

                if ($user != null) {
                    $data['user_id'] = $user->id;
                } else {
                    Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                }
                $osm2caiMedia->updateOrCreate(['geohub_id' => $id], $data);
            }
            if ($osm2caiMedia->updated_at < $updated_at) {
                $user = User::whereEmail($mediaGeojson['properties']['user_email'])->first();
                $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($mediaGeojson['geometry']) . '\')');
                $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
                $data = [
                    'name' => $mediaGeojson['properties']['name'],
                    'geometry' => $geometry,
                    'raw_data' => $mediaGeojson['properties']['raw_data'],
                    'updated_at' => $mediaGeojson['properties']['updated_at'],
                ];
                if ($user != null) {
                    $data['user_id'] = $user->id;
                } else {
                    Log::channel('missingUsers')->info('User with email ' . $poiGeoJson['properties']['user_email'] . ' not found');
                }
                $osm2caiMedia->updateOrCreate(['geohub_id' => $id], $data);

                $updatedElements[] = 'Media with id ' . $id . ' updated';
            }
        }

        return view('importedUgc', [
            'updatedElements' => $updatedElements,
            'poiCreatedElements' => $poiCreatedElements,
            'trackCreatedElements' => $trackCreatedElements,
            'mediaCreatedElements' => $mediaCreatedElements
        ]);
    }

    private function getGeojson($url)
    {
        $geojson = json_decode(file_get_contents($url), true);
        return $geojson;
    }
}
