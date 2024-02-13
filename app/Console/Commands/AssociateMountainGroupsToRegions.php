<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\MountainGroups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssociateMountainGroupsToRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:associate-mountain-groups-to-regions {name? : The region name to query on. Not mandatory. If not provided, all regions will be processed.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate mountain groups to regions based on geometric data';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        if (!$this->argument('name')) {
            $regions = Region::all();
            foreach ($regions as $region) {
                $this->info("Processing region: {$region->name}");

                //mountain groups association
                $mountainGroups = DB::table('mountain_groups')
                    ->join('mountain_groups_region', 'mountain_groups.id', '=', 'mountain_groups_region.mountain_group_id')
                    ->select('mountain_groups.*')
                    ->whereRaw(
                        'ST_Intersects(mountain_groups.geometry, ST_Transform(ST_SetSRID(?::geometry, 4326), 4326))',
                        [$region->geometry]
                    )
                    ->get();

                foreach ($mountainGroups as $mountainGroup) {
                    $region->mountainGroups()->syncWithoutDetaching([$mountainGroup->id]);
                }

                //ec pois association
                $poisToUpdate = DB::table('ec_pois')
                    ->select('ec_pois.*')
                    ->whereRaw(
                        'ST_Within(ec_pois.geometry::geometry, ST_SetSRID(?::geometry, 4326))',
                        [$region->geometry]
                    )
                    ->get();

                foreach ($poisToUpdate as $poi) {
                    DB::table('ec_pois')
                        ->where('id', $poi->id)
                        ->update(['region_id' => $region->id]);
                }

                $this->info("Associated " . count($mountainGroups) . " mountain groups and " . count($poisToUpdate) . " ec pois to region: {$region->name}");
            }
        } else {
            $region = Region::where('name', $this->argument('name'))->first();
            if ($region) {
                $this->info("Processing region: {$region->name}");

                //mountain groups association
                $mountainGroups = MountainGroups::select('mountain_groups.*')
                    ->join(DB::raw('mountain_groups_region'), function ($join) use ($region) {
                        $join->on('mountain_groups.id', '=', 'mountain_groups_region.mountain_group_id')
                            ->whereRaw('ST_Intersects(mountain_groups.geometry, ?)', [$region->geometry])
                            ->orWhereRaw('ST_Contains(?, mountain_groups.geometry)', [$region->geometry]);
                    })
                    ->get();

                foreach ($mountainGroups as $mountainGroup) {
                    $region->mountainGroups()->syncWithoutDetaching([$mountainGroup->id]);
                }

                //ec pois association
                $poisToUpdate = DB::table('ec_pois')
                    ->select('ec_pois.*')
                    ->whereRaw(
                        'ST_Within(ec_pois.geometry::geometry, ST_SetSRID(?::geometry, 4326))',
                        [$region->geometry]
                    )
                    ->get();

                foreach ($poisToUpdate as $poi) {
                    DB::table('ec_pois')
                        ->where('id', $poi->id)
                        ->update(['region_id' => $region->id]);
                }

                $this->info("Associated " . count($mountainGroups) . " mountain groups and " . count($poisToUpdate) . " ec pois to region: {$region->name}");
            } else {
                $this->error("Region not found: {$this->argument('name')}");
            }
        }


        $this->info("All regions processed.");
    }
}
