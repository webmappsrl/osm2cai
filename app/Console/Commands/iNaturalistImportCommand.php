<?php

namespace App\Console\Commands;

use App\Models\UgcPoi;
use App\Models\UgcMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class iNaturalistImportCommand extends Command
{
    protected $signature = 'osm2cai:inaturalist-import {ids?*} {--file= : File path containing IDs}';
    protected $description = 'Importa UGC POI da iNaturalist';

    // Definizione delle costanti
    protected const INATURALIST_EMAIL = 'inaturalist@email.com';
    protected const INATURALIST_NAME = 'iNaturalist';
    protected const DEFAULT_PASSWORD = 'inaturalist123';
    protected const DEFAULT_TYPE = 'poi';
    protected const BASE_OBSERVATION_URL = 'https://www.inaturalist.org/observations/';
    protected $ancestorIds = [47126 => 'Flora', 1 => 'Fauna'];

    public function handle()
    {
        $ids = $this->getObservationIds();

        if (empty($ids)) {
            $this->error('Nessun ID fornito.');
            return 1;
        }

        $this->createInaturalistUser();

        foreach ($ids as $id) {
            $this->importObservation($id);
        }

        $this->info('Importazione completata.');
        return 0;
    }

    /**
     * Recupera gli ID delle osservazioni dal comando o da un file.
     *
     * @return array
     */
    private function getObservationIds(): array
    {
        $ids = $this->argument('ids');
        $filePath = $this->option('file');

        if ($filePath) {
            $fileContents = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $ids = array_merge($ids, $fileContents);
        }

        return $ids;
    }

    /**
     * Crea un utente iNaturalist se non esiste.
     */
    private function createInaturalistUser(): void
    {
        if (DB::table('users')->where('email', self::INATURALIST_EMAIL)->doesntExist()) {
            $this->info('Creating user iNaturalist...');

            DB::table('users')->insert([
                'name' => self::INATURALIST_NAME,
                'email' => self::INATURALIST_EMAIL,
                'email_verified_at' => now(),
                'password' => bcrypt(self::DEFAULT_PASSWORD),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Importa un'osservazione da iNaturalist e gestisce i dati di UgcPoi e UgcMedia.
     *
     * @param int $id
     */
    private function importObservation(int $id): void
    {
        $data = $this->fetchObservationData($id);
        if (!$data) {
            $this->logObservationError($id, "Osservazione non trovata.");
            return;
        }

        $ancestor = $this->getObservationType($data, $id);
        if (!$ancestor) {
            return;
        }

        $geometry = $this->getObservationGeometry($data, $id);
        if (!$geometry) {
            return;
        }

        $this->saveObservation($data, $ancestor, $geometry, $id);
    }

    /**
     * Recupera i dati dell'osservazione da iNaturalist.
     *
     * @param int $id
     * @return array|null
     */
    private function fetchObservationData(int $id): ?array
    {
        $response = Http::get("https://api.inaturalist.org/v1/observations/{$id}");

        if ($response->failed()) {
            $this->logObservationError($id, "Errore nel recupero dell'osservazione");
            return null;
        }

        return $response->json()['results'][0] ?? null;
    }

    /**
     * Logga un errore relativo all'osservazione.
     *
     * @param int $id
     * @param string $message
     */
    private function logObservationError(int $id, string $message): void
    {
        $this->error("{$message} con ID {$id}");
        Log::info("{$message} con ID {$id}");
    }

    /**
     * Determina il tipo di osservazione (Flora, Fauna, etc.).
     *
     * @param array $data
     * @param int $id
     * @return string|null
     */
    private function getObservationType(array $data, int $id): ?string
    {
        $ancestor = $this->ancestorIds[$data['taxon']['ancestor_ids'][1] ?? null] ?? null;

        if (!$ancestor) {
            $this->logObservationError($id, "Tipo di acquisizione non trovato");
        }

        return $ancestor;
    }

    /**
     * Recupera e valida la geometria dell'osservazione.
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    private function getObservationGeometry(array $data, int $id)
    {
        $geojson = $data['geojson'] ?? null;

        if (!$geojson) {
            $this->logObservationError($id, "Coordinate geografiche non disponibili");
            return null;
        }

        $geojsonString = json_encode($geojson);
        return DB::raw("ST_GeomFromGeoJSON('{$geojsonString}')");
    }

    /**
     * Salva l'osservazione e i media associati.
     *
     * @param array $data
     * @param string $ancestor
     * @param mixed $geometry
     * @param int $id
     */
    private function saveObservation(array $data, string $ancestor, $geometry, int $id): void
    {
        $observationUri = $data['uri'] ?? self::BASE_OBSERVATION_URL . $id;
        $description = $this->buildDescription($data, $observationUri);
        $rawData = $this->buildRawData($data, $ancestor, $observationUri);

        $iNaturalistUserId = $this->getInaturalistUserId();

        // Crea o aggiorna il modello UgcPoi
        $ugcPoi = UgcPoi::updateOrCreate(
            ['name' => $data['species_guess'] ?? 'Sconosciuto', 'app_id' => self::INATURALIST_NAME],
            [
                'geometry' => $geometry,
                'type' => $ancestor,
                'form_id' => self::DEFAULT_TYPE,
                'raw_data' => $rawData,
                'user_id' => $iNaturalistUserId
            ]
        );

        $this->handleAssociatedMedia($data['photos'] ?? [], $description, $geometry, $ugcPoi, $iNaturalistUserId);
        $this->info("Osservazione con ID {$id} importata con successo.");
    }

    /**
     * Costruisce la descrizione dell'osservazione.
     *
     * @param array $data
     * @param string $observationUri
     * @return string
     */
    private function buildDescription(array $data, string $observationUri): string
    {
        $description = $data['description'] ?? '';
        return $description . "\nsource: " . $observationUri;
    }

    /**
     * Costruisce i dati raw per l'inserimento nel database.
     *
     * @param array $data
     * @param string $ancestor
     * @param string $observationUri
     * @return array
     */
    private function buildRawData(array $data, string $ancestor, string $observationUri): array
    {
        $location = explode(',', $data['location']);
        $latitude = $location[0] ?? null;
        $longitude = $location[1] ?? null;

        return [
            'title' => $data['species_guess'] ?? 'Sconosciuto',
            'description' => $this->buildDescription($data, $observationUri),
            'waypointtype' => $ancestor,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'app_id' => self::INATURALIST_NAME,
            'form_id' => self::DEFAULT_TYPE,
            'uri' => $observationUri
        ];
    }

    /**
     * Recupera l'ID dell'utente iNaturalist.
     *
     * @return int
     */
    private function getInaturalistUserId(): int
    {
        return DB::table('users')
            ->where('email', self::INATURALIST_EMAIL)
            ->first()
            ->id;
    }

    /**
     * Gestisce i media associati all'osservazione.
     *
     * @param array $photos
     * @param string $description
     * @param mixed $geometry
     * @param UgcPoi $ugcPoi
     * @param int $iNaturalistUserId
     */
    private function handleAssociatedMedia(array $photos, string $description, $geometry, UgcPoi $ugcPoi, int $iNaturalistUserId): void
    {
        foreach ($photos as $photo) {
            $url = $photo['url'] ?? '';

            $ugcMedia = UgcMedia::updateOrCreate(
                ['relative_url' => $url, 'name' => $ugcPoi->name],
                [
                    'app_id' => self::INATURALIST_NAME,
                    'description' => $description,
                    'user_id' => $iNaturalistUserId,
                    'geometry' => $geometry,
                ]
            );

            if ($ugcMedia->wasRecentlyCreated) {
                $ugcPoi->ugc_media()->save($ugcMedia);
            }
        }
    }
}
