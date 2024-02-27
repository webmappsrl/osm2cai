<?php

namespace App\Console\Commands;

use App\Models\EcPoi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UpdateEcPoisOsmType extends Command
{
    protected $signature = 'osm2cai:update-ec-pois-osm-type';
    protected $description = 'Update the EC POIs OSM type, when null, checking the OSM APIs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Retrieve all EC POIs records that have a null or empty 'osm_type'
        $ecPois = DB::table('ec_pois')->whereNull('osm_type')->orWhere('osm_type', '')->get();

        foreach ($ecPois as $ecPoi) {
            $types = ['node', 'way', 'relation'];
            $foundTypes = [];

            foreach ($types as $type) {
                $response = $this->osmElementExists($ecPoi->osm_id, $type);
                if ($response['exists']) {
                    // Collect found types and their corresponding names
                    $foundTypes[$type] = $response['name'];
                }
            }

            // Update the EC POIs record if exactly one type is found
            if (count($foundTypes) === 1) {
                $type = key($foundTypes);
                DB::table('ec_pois')->where('id', $ecPoi->id)->update(['osm_type' => substr(ucfirst($type), 0, 1)]);
                $this->info("Updated EC POI {$ecPoi->id} with OSM type: " . ucfirst($type));
            } elseif (count($foundTypes) > 1) {
                // In case of multiple types, match by name and update
                foreach ($foundTypes as $type => $name) {
                    if ($name === $ecPoi->name) {
                        DB::table('ec_pois')->where('id', $ecPoi->id)->update(['osm_type' => substr(ucfirst($type), 0, 1)]);
                        $this->info("Updated EC POI {$ecPoi->id} with OSM type: " . ucfirst($type) . " after name matching");
                        break;
                    }
                }
            }
        }

        $this->info('OSM types synced with EcPois records successfully.');
    }

    private function osmElementExists($osmId, $type)
    {
        // Construct the URL for the OSM API request
        $url = "https://www.openstreetmap.org/api/0.6/$type/$osmId";
        $response = Http::get($url);

        if ($response->ok()) {
            // Parse the XML response
            $xml = simplexml_load_string($response->body());
            $json = json_encode($xml);
            $array = json_decode($json, true);
            $name = $array[$type]['@attributes']['name'] ?? '';

            // Extract the name from the OSM element tags
            if (isset($array[$type]['tag'])) {
                foreach ($array[$type]['tag'] as $tag) {
                    if ($tag['@attributes']['k'] === 'name') {
                        $name = $tag['@attributes']['v'];
                        break;
                    }
                }
            }

            // Fallback name format in case no name tag is found (same as in the importPois action https://github.com/webmappsrl/osm2cai/blob/3aa7392e5dc385fd2837f44ab1c9f2a219dc1eb4/app/Nova/Actions/ImportPois.php#L92-L93)
            if ($name == '') {
                $name = 'no name (' . $type . '/' . $osmId . ')';
            }

            return ['exists' => true, 'name' => $name];
        } else {
            return ['exists' => false];
        }
    }
}
