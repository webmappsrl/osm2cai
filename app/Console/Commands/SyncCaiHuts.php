<?php

namespace App\Console\Commands;

use App\Models\CaiHuts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCaiHuts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:sync_caihuts';

    protected $description = 'Sync Cai Huts from CAI DB';

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
        $createdHuts = 0;
        $updatedHuts = 0;

        // there is a request limit for the api, implemented a backoff logic
        // $attempt = 0;
        // $maxAttempts = 5;
        // $backoff = 1;

        $hutsListApi = 'https://rifugi.cai.it/api/v1/shelters/updatedAt';
        try {
            $hutsList = json_decode(file_get_contents($hutsListApi), true);
        } catch (\Exception $e) {
            $this->error("Error fetching huts list: {$e->getMessage()}");
            return 1;
        }
        $hutsList = json_decode(file_get_contents($hutsListApi), true) ?? [];

        foreach ($hutsList as $unicoId => $updatedAt) {
            $hutApi = 'https://rifugi.cai.it/api/v1/shelters/geojson/' . $unicoId;
            // while ($attempt < $maxAttempts) {
            try {
                $hutData = json_decode(file_get_contents($hutApi), true);
            } catch (\Exception $e) {
                // $attempt++;
                // sleep($backoff);
                // $backoff *= 4;
                $this->error("Error fetching hut data for unico_id: {$unicoId} . {$e->getMessage()}");
                // $this->info("Retrying attempt: {$attempt} in {$backoff} seconds");
            }
            // }
            // if ($attempt >= $maxAttempts) {
            //     $this->error("API request limit reached");
            //     return 1;
            // }

            try {
                $geometryJson =  json_encode($hutData['geometry']);
                $geometry = DB::raw("ST_SetSRID(ST_GeomFromGeoJSON('{$geometryJson}'), 4326)");
            } catch (\Exception $e) {
                $this->error("Error parsing geometry for unico_id: {$unicoId} . {$e->getMessage()}");
                continue;
            }

            try {
                $props = $hutData['properties'] ?? [];
                $mappedDataForUpdate = [
                    'name' => $props['official_name'] ?? null,
                    'second_name' => $props['second_official_name'] ?? null,
                    'description' => $props['description'] ?? null,
                    'elevation' => $props['elevation'] ?? null,
                    'owner' => $props['owner'] ?? null,
                    'geometry' => $geometry,
                    'gallery' => $props['url'] ?? null, //https://orchestrator.maphub.it/resources/stories/2860
                    'addr_street' => $props['address'] ?? null,
                    'phone' => $props['operating_phone'] ?? null,
                    'email' => $props['operating_email'] ?? null,

                ];
            } catch (\Exception $e) {
                Log::error("Error parsing properties for unico_id: {$unicoId} . {$e->getMessage()}");
                $this->error("Error parsing properties for unico_id: {$unicoId} . {$e->getMessage()}");
                continue;
            }

            $hut = CaiHuts::where('unico_id', $unicoId)->first();
            if ($hut) {
                if ($hut->updated_at->timestamp < strtotime($updatedAt)) {
                    $this->info("Updating hut with unico_id: {$unicoId}");
                    $hut->update($mappedDataForUpdate);
                    $updatedHuts++;
                } else {
                    $this->info("Hut with unico_id: {$unicoId} is already up to date");
                    continue;
                }
            } else {
                $this->info("Creating hut with unico_id: {$unicoId}");
                $hut = CaiHuts::create(array_merge(['unico_id' => $unicoId], $mappedDataForUpdate));
                $createdHuts++;
            }
        }
        $this->info("Created huts: {$createdHuts}");
        $this->info("Updated huts: {$updatedHuts}");
        $this->info("Sync completed");
        return 0;
    }
}
