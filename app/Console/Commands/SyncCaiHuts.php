<?php

namespace App\Console\Commands;

use App\Models\CaiHuts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            try {
                $hutData = json_decode(file_get_contents($hutApi), true);
                break;
            } catch (\Exception $e) {
                $this->error("Error fetching hut data for unico_id: {$unicoId} . {$e->getMessage()}");
            }

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
                    'name' => $props['official_name'],
                    'second_name' => $props['second_official_name'],
                    'description' => $props['description'],
                    'elevation' => $props['elevation'],
                    'owner' => $props['owner'],
                    'geometry' => $geometry
                ];
            } catch (\Exception $e) {
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
