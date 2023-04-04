<?php

namespace App\Console\Commands;

use App\Models\HikingRoute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class tdhExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:tdh_export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $filePath = 'public/tdh-export.geojson';
        Storage::put($filePath,'');
        Storage::append($filePath,'{"type":"FeatureCollection","features":[');

        $hikingroutes = collect(DB::select('select id from hiking_routes;'))->pluck('id')->toArray();

        $tot = HikingRoute::count();
        $count = 1;
        foreach($hikingroutes as $id ){
            $route = HikingRoute::find($id);

            $geometry = DB::select("SELECT ST_AsGeoJSON('$route->geometry') As g")[0]->g;

            $feature = [
                "type" => "Feature",
                "properties" => [
                    "id" => $route->id,
                    "created_at" => $route->created_at,
                    "updated_at" => $route->updated_at,
                    "osm2cai_status" => $route->osm2cai_status,
                    "validation_date" => $route->validation_date,
                    "relation_id" => $route->relation_id,
                    "ref" => $route->ref,
                    "ref_REI" => $route->ref_REI,
                    "gpx_url" => "TO_BE_IMPLEMENTED",
                    "cai_scale" => $route->cai_scale,
                    "cai_scale_string" => "TO_BE_IMPLEMENTED",
                    "survey_date" => $route->survey_date,
                    "from" => $route->from,
                    "from_geometry" => "TO_BE_IMPLEMENTED",
                    "city_from" => "TO_BE_IMPLEMENTED",
                    "city_from_istat" => "TO_BE_IMPLEMENTED",
                    "region_from" => "TO_BE_IMPLEMENTED",
                    "region_from_istat" => "TO_BE_IMPLEMENTED",
                    "to" => $route->to,
                    "to_geometry" => "TO_BE_IMPLEMENTED",
                    "city_to" => "TO_BE_IMPLEMENTED",
                    "city_to_istat" => "TO_BE_IMPLEMENTED",
                    "region_to" => "TO_BE_IMPLEMENTED",
                    "region_to_istat" => "TO_BE_IMPLEMENTED",
                    "name" => $route->name,
                    "round_trip" => "TO_BE_IMPLEMENTED",
                    "abstract" => "TO_BE_IMPLEMENTED",
                    "description" => "TO_BE_IMPLEMENTED",
                    "distance" => $route->distance,
                    "ascent" => "TO_BE_IMPLEMENTED",
                    "descent" => "TO_BE_IMPLEMENTED",
                    "duration_forward" => "TO_BE_IMPLEMENTED",
                    "duration_backward" => "TO_BE_IMPLEMENTED",
                    "ele_from" => "TO_BE_IMPLEMENTED",
                    "ele_to" => "TO_BE_IMPLEMENTED",
                    "ele_max" => "TO_BE_IMPLEMENTED",
                    "ele_min" => "TO_BE_IMPLEMENTED",
                ],
                "geometry" => json_decode($geometry,true)
            ];
            
            $this->info('adding route ' . $route->id . ' number ' . $count . ' from ' . $tot);
            Storage::append($filePath,json_encode($feature));
            if ($count < $tot) {
                Storage::append($filePath,',');
            }
            $count++;

        }

        Storage::append($filePath,']}');
    }
}
