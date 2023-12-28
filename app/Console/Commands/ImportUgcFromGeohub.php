<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UgcPoi;
use App\Models\UgcMedia;
use App\Models\UgcTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportUgcFromGeohub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:sync-ugc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command syncs ugc from geohub to osm2cai db using geohub api';

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
                $this->info("Starting sync for $type from $endPoint");
                $list = json_decode($this->get_content($endPoint), true);

                foreach ($list as $id => $updated_at) {
                    $this->info('Checking ' . $type . ' with id ' . $id);
                    $model = $this->getModel($type, $id);
                    $geoJson = $this->getGeojson("https://geohub.webmapp.it/api/ugc/{$type}/geojson/{$id}/osm2cai");

                    if ($model->wasRecentlyCreated) {
                        $createdElements[$type]++;
                        $this->info("Created new $type with id $id");
                    }

                    if ($model->updated_at < $updated_at || $model->wasRecentlyCreated) {
                        $this->syncRecord($model, $geoJson, $id);
                        if ($model->updated_at < $updated_at) {
                            $updatedElements[] = ucfirst($type) . ' with id ' . $id . ' updated';
                            $this->info("Updated $type with id $id");
                        }
                    }
                }
            }
            $this->info("Finished sync. Created: " . implode(', ', $createdElements) . ", Updated: " . count($updatedElements));
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
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
            throw new \Exception("Failed to fetch GeoJSON from URL: $url");
        }
        return $geoJson;
    }

    private function syncRecord($model, $geoJson, $id)
    {
        $user = DB::raw('(SELECT id FROM users WHERE email = \'' . $geoJson['properties']['user_email'] . '\')');
        $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($geoJson['geometry']) . '\')');
        $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');

        $data = [
            'name' => $geoJson['properties']['name'],
            'geometry' => $geometry,
            'raw_data' => $geoJson['properties']['raw_data'],
            'updated_at' => $geoJson['properties']['updated_at'],
            'taxonomy_wheres' => $geoJson['properties']['taxonomy_wheres'],
        ];

        if ($user != null) {
            $data['user_id'] = $user->id;
        } else {
            Log::channel('missingUsers')->info('User with email ' . $geoJson['properties']['user_email'] . ' not found');
        }
        if ($model instanceof UgcMedia) {
            $data['relative_url'] = $geoJson['url'];
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
            throw new \Exception("Failed to fetch content from URL: $url");
        }
        curl_close($ch);
        return $data;
    }
}
