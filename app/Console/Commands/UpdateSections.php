<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symm\Gisconverter\Geometry\Point;

class UpdateSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the sections table retrieving the data from overpass';

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
        $overpassSectionsApi = 'https://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A250%5D%3B%0Aarea%28id%3A3600365331%29-%3E.searchArea%3B%0Anwr%5B%22office%22%3D%22association%22%5D%5B%22operator%22%3D%22Club%20Alpino%20Italiano%22%5D%28area.searchArea%29%3B%0Aout%20geom%3B';
        $response = Http::get($overpassSectionsApi);
        $data = $response->json();
        $sections = $data['elements'];

        foreach ($sections as $section) {
            $this->info('Processing section ' . $section['tags']['name'] . '...');

            //if lat and lon are not present, check if bounds are present and calculate the centroid
            if (!isset($section['lat']) || !isset($section['lon'])) {
                $this->info('lat or lon not found');
                if (isset($section['bounds'])) {
                    $this->info('bounds found, calculating the centroid...');
                    //create a geometry using the bounds
                    $bounds = $section['bounds'];
                    $minLat = $bounds['minlat'];
                    $minLon = $bounds['minlon'];
                    $maxLat = $bounds['maxlat'];
                    $maxLon = $bounds['maxlon'];

                    //calculate the centroid
                    $centroidLat = ($minLat + $maxLat) / 2;
                    $centroidLon = ($minLon + $maxLon) / 2;
                    $point = new Point([$centroidLon, $centroidLat]);
                    $geometry = $point->toWKT();
                }
            } else {
                $lat = $section['lat'];
                $lon = $section['lon'];
                $point = new Point([$lon, $lat]);
                $geometry = $point->toWKT();
            }
            //if the source:ref tag is not present, try to match the name
            if (!isset($section['tags']['source:ref'])) {
                $this->info('source:ref tag not found, finding the corresponding section in the database matching the name...');
                if (isset($section['tags']['name'])) {
                    $name = $section['tags']['name'];
                    //in every name get only the last word from the string
                    $name = explode(' ', $name);
                    $name = end($name);
                    $this->info('searching section in the database with name ' . $name . '...');

                    $results =  DB::table('sections')->where('name', 'ILIKE', '%' . $name . '%')->get();

                    if ($results->count() < 1) {
                        $this->info('section not found in the database with name ' . $name . ' skipping...');
                        continue;
                    } else if ($results->count() > 1) {
                        $this->info('multiple sections found with the same name, ' . $name . '  skipping...');
                        continue;
                    }

                    DB::table('sections')->where('name', 'ILIKE', '%' . $name . '%')->update(['geometry' => $geometry]);

                    $this->info('geometry updated for section ' . $name . ' using the name tag');
                } else {
                    $this->info('name tag not found, skipping section...');
                    continue;
                }
                continue;
            } else {
                $caiCode = $section['tags']['source:ref'];
                $update = [
                    'geometry' => $geometry,
                    'addr_city' => isset($section['tags']['addr:city']) ? $section['tags']['addr:city'] : null,
                    'addr_street' => isset($section['tags']['addr:street']) ? $section['tags']['addr:street'] : null,
                    'addr_housenumber' => isset($section['tags']['addr:housenumber']) ? $section['tags']['addr:housenumber'] : null,
                    'addr_postcode' => isset($section['tags']['addr:postcode']) ? $section['tags']['addr:postcode'] : null,
                    'website' => $section['tags']['website'] ?? $section['tags']['contact:website'] ?? null,
                    'phone' => $section['tags']['phone'] ?? $section['tags']['contact:phone'] ?? null,
                    'email' => $section['tags']['email'] ?? $section['tags']['contact:email'] ?? null,
                    'opening_hours' => isset($section['tags']['opening_hours']) ? $section['tags']['opening_hours'] : null,
                    'wheelchair' => isset($section['tags']['wheelchair']) ? $section['tags']['wheelchair'] : null,
                    'fax' => isset($section['tags']['fax']) ? $section['tags']['fax'] : null,
                ];
            }

            DB::table('sections')->where('cai_code', $caiCode)->update($update);

            $this->info('updated section ' . $caiCode . ' using the source:ref tag');
        }

        $this->info('All Sections are updated correctly!');
        return 0;
    }
}
