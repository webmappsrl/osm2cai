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
    protected $signature = 'osm2cai:sync-ugc {app_id?}';

    protected $logger;

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
        $this->logger = Log::channel('import-ugc');
    }

    private $baseApiUrl = "https://geohub.webmapp.it/api/ugc/";
    private $apps = [
        20 => 'it.webmapp.sicai',
        26 => 'it.webmapp.osm2cai',
        58 => 'it.webmapp.acquasorgente'
    ];
    private $types = ['poi', 'track', 'media'];
    private $createdElements = [];
    private $updatedElements = [];
    private $failedElements = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $appId = $this->argument('app_id');
        if ($appId) {
            $this->syncApp($appId);
        } else {
            $this->syncAllApps();
        }

        $this->updateFormIds();

        $this->logResults();

        $this->logger->info("Sync completato.");
        $this->info("Sync completato.");

        return [
            'createdElements' => $this->createdElements,
            'updatedElements' => $this->updatedElements,
            'failedElements' => $this->failedElements
        ];
    }

    private function syncAllApps()
    {
        foreach ($this->apps as $appId => $appName) {
            $this->syncApp($appId);
        }
    }


    private function syncApp($appId)
    {
        $this->logger->info("Avvio sync per l'app con ID $appId");
        $this->info("Avvio sync per l'app con ID $appId");
        $appName = $this->apps[$appId] ?? null;

        if (!$appName) {
            $this->logger->error("ID app non valido: $appId");
            $this->error("ID app non valido: $appId");
            return;
        }

        foreach ($this->types as $type) {
            $endpoint = "{$this->baseApiUrl}{$type}/geojson/{$appId}/list";
            $this->syncType($type, $endpoint, $appId);
        }
    }

    private function syncType($type, $endpoint, $appId)
    {
        $this->logger->info("Effettuando il sync per $type da $endpoint");
        $list = json_decode($this->get_content($endpoint), true);
        if (empty($list)) {
            $this->logger->info("Nessun elemento da sincronizzare per $type da $endpoint");
            $this->info("Nessun elemento da sincronizzare per $type da $endpoint");
            return;
        }

        foreach ($list as $id => $updated_at) {
            $this->syncElement($type, $id, $updated_at, $appId);
        }
    }

    private function get_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        $data = curl_exec($ch);
        if ($data === false) {
            $this->logger->error("Failed to fetch content from URL: $url");
            $this->error("Failed to fetch content from URL: $url");
            throw new \Exception("Failed to fetch content from URL: $url");
        }
        curl_close($ch);
        return $data;
    }

    private function syncElement($type, $id, $updated_at, $appId)
    {
        try {
            $model = $this->getModel($type, $id);
            $geoJson = $this->getGeojson("https://geohub.webmapp.it/api/ugc/{$type}/geojson/{$id}/osm2cai");

            $needsUpdate = $model->wasRecentlyCreated || $model->updated_at < $updated_at;

            if ($needsUpdate) {
                $this->syncRecord($model, $geoJson, $id, $appId, $type);
                if ($model->wasRecentlyCreated) {
                    $this->createdElements[] = [
                        'type' => $type,
                        'id' => $id,
                        'app' => $this->apps[$appId]
                    ];
                } else {
                    $this->updatedElements[] = [
                        'type' => $type,
                        'id' => $id,
                        'app' => $this->apps[$appId]
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->failedElements[] = [
                'type' => $type,
                'id' => $id,
                'app' => $this->apps[$appId],
                'error' => $e->getMessage()
            ];
            $this->logger->error("Errore durante la sincronizzazione di $type ID $id per app {$this->apps[$appId]}: " . $e->getMessage());
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
            $this->logger->error("Errore nel fetch del GeoJSON da $url");
            $this->error("Errore nel fetch del GeoJSON da $url");
            throw new \Exception("Errore nel fetch del GeoJSON da $url");
        }
        return $geoJson;
    }

    private function syncRecord($model, $geoJson, $id, $appId, $type)
    {
        $this->logger->info("Aggiornamento $type con id $id");
        $this->info("Aggiornamento $type con id $id");

        $data = [
            'name' => $geoJson['properties']['name'],
            'raw_data' => json_decode($geoJson['properties']['raw_data'], true),
            'updated_at' => $geoJson['properties']['updated_at'],
            'taxonomy_wheres' => $geoJson['properties']['taxonomy_wheres'],
            'app_id' => 'geohub_' . $appId,
        ];

        $user = User::where('email', $geoJson['properties']['user_email'])->first();
        $geometry = null;
        if (
            isset($geoJson['geometry']) &&
            $geoJson['geometry'] !== null &&
            !empty($geoJson['geometry']) &&
            isset($geoJson['geometry']['coordinates'])
        ) {
            $geometry = DB::raw('ST_GeomFromGeoJSON(\'' . json_encode($geoJson['geometry']) . '\')');
            $geometry = DB::raw('ST_Transform(' . $geometry . ', 4326)');
        }

        if ($geometry) {
            $data['geometry'] = $geometry;
        }

        if ($model instanceof UgcPoi) {
            $rawData = json_decode($geoJson['properties']['raw_data'], true);
            $data['form_id'] = $rawData['id'] ?? null;
        }

        if ($user) {
            $data['user_id'] = $user->id;
        } else {
            Log::channel('missingUsers')->info('Utente con email ' . $geoJson['properties']['user_email'] . ' non trovato');
            $data['user_no_match'] = $geoJson['properties']['user_email'];
        }

        if ($model instanceof UgcMedia) {
            $data['relative_url'] = $geoJson['properties']['url'] ?? null;
            $poisGeohubIds = $geoJson['properties']['ugc_pois'] ?? [];
            $tracksGeohubIds = $geoJson['properties']['ugc_tracks'] ?? [];

            if (!empty($poisGeohubIds)) {
                $poisIds = UgcPoi::whereIn('geohub_id', $poisGeohubIds)->pluck('id')->toArray();
                $model->ugc_pois()->sync($poisIds);
            }
            if (!empty($tracksGeohubIds)) {
                $tracksIds = UgcTrack::whereIn('geohub_id', $tracksGeohubIds)->pluck('id')->toArray();
                $model->ugc_tracks()->sync($tracksIds);
            }
        }

        $model->update($data);
        $model->save();
        $this->logger->info("Aggiornamento completato");
        $this->info("Aggiornamento completato");
    }

    private function updateFormIds()
    {
        $ugcPois = DB::table('ugc_pois')->whereNull('form_id')->get();
        foreach ($ugcPois as $ugcPoi) {
            $rawData = json_decode($ugcPoi->raw_data, true);
            DB::table('ugc_pois')->where('id', $ugcPoi->id)->update(['form_id' => $rawData['id'] ?? null]);
        }
    }

    private function logResults()
    {
        $this->logger->info("=== RIEPILOGO SINCRONIZZAZIONE ===");

        $this->logger->info("ELEMENTI CREATI (" . count($this->createdElements) . "):");
        foreach ($this->createdElements as $element) {
            $this->logger->info("{$element['type']} ID: {$element['id']} - App: {$element['app']}");
        }

        $this->logger->info("ELEMENTI AGGIORNATI (" . count($this->updatedElements) . "):");
        foreach ($this->updatedElements as $element) {
            $this->logger->info("{$element['type']} ID: {$element['id']} - App: {$element['app']}");
        }

        $this->logger->info("ELEMENTI FALLITI (" . count($this->failedElements) . "):");
        foreach ($this->failedElements as $element) {
            $this->logger->info("{$element['type']} ID: {$element['id']} - App: {$element['app']} - Errore: {$element['error']}");
        }
    }
}
