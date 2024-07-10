<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssociateToMoutainGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:associate-to-mountain-groups {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the mountain_groups table with the aggregated data for pois, caihuts, hiking routes and sections';

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
        $dataToRetrieve = [
            'ec_pois_count' => 'ec_pois',
            'cai_huts_count' => 'cai_huts',
            'hiking_routes_count' => 'hiking_routes',
            'sections_count' => 'sections'
        ];

        if ($this->argument('id')) {
            $mountainGroups = DB::table('mountain_groups')->where('id', $this->argument('id'))->get();
        } else {
            $mountainGroups = DB::table('mountain_groups')->get();
        }

        foreach ($mountainGroups as $mountainGroup) {
            $aggregatedData = [];
            foreach ($dataToRetrieve as $key => $table) {
                //get the record for each $table based on a postgis intersect query with the mountain group geometry
                $aggregatedData[$key] = DB::table($table)
                    ->whereRaw("ST_Intersects(geometry, (SELECT geometry FROM mountain_groups WHERE id = ?))", [$mountainGroup->id])
                    ->count();
            }
            $aggregatedData['poi_total'] = $aggregatedData['ec_pois_count'] + $aggregatedData['cai_huts_count'];

            DB::table('mountain_groups')->where('id', $mountainGroup->id)->update(['aggregated_data' => json_encode($aggregatedData)]);

            $this->info("Aggregated data for mountain group {$mountainGroup->name} updated");
        }

        return 0;
    }
}
