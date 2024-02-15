<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\MountainGroups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\SchemaOrg\HinduTemple;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class AssociateMountainGroupsToRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:associate-to-regions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate the specified model to regions based on geometric data';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {

        //create an array with all the regions name taken from the database and add the option "all"
        $regions = Region::all();
        $regionNames = $regions->pluck('name')->toArray();
        array_unshift($regionNames, 'all');
        $resource = $this->choice('Which resource do you want to associate to regions?', ['mountain_groups', 'ec_pois', 'huts', 'all'], 'all');
        $regionName = $this->choice('Which region do you want to associate the resource to?', $regionNames, 'all');


        if ($regionName === 'all') {
            $regions = Region::all();
            foreach ($regions as $region) {

                switch ($resource) {
                    case 'mountain_groups':
                        $this->associateMountainGroupsToRegion($region);
                        break;
                    case 'ec_pois':
                        $this->associateEcPoisToRegion($region);
                        break;
                    case 'huts':
                        $this->associateHutsToRegion($region);
                        break;
                    case 'all':
                        $this->associateAllToRegion($region);
                        break;
                    default:
                        throw new \Exception("Resource not found: {$resource}");
                        break;
                }
            }
        } else {
            $region = Region::where('name', $regionName)->first();
            if ($region) {
                switch ($resource) {
                    case 'mountain_groups':
                        $this->associateMountainGroupsToRegion($region);
                        break;
                    case 'ec_pois':
                        $this->associateEcPoisToRegion($region);
                        break;
                    case 'all':
                        $this->associateAllToRegion($region);
                        break;
                    default:
                        throw new \Exception("Resource not found: {$resource}");
                        break;
                }
            } else {
                throw new \Exception("Region not found: {$regionName}");
            }
        }
    }

    protected function associateMountainGroupsToRegion($region)
    {
        $regionGeometry = $region->geometry;
        try {
            $mountainGroups = DB::table('mountain_groups')
                ->select('mountain_groups.id')
                ->whereRaw("ST_Intersects(mountain_groups.geometry::geometry, ST_GeomFromEWKB(decode(?, 'hex')))", [$regionGeometry])
                ->orWhereRaw("ST_Within(mountain_groups.geometry::geometry, ST_GeomFromEWKB(decode(?, 'hex')))", [$regionGeometry])
                ->get();
        } catch (\Exception $e) {
            throw $e;
            return;
        }

        foreach ($mountainGroups as $mountainGroup) {
            DB::table(('mountain_groups_region'))
                ->insert([
                    'mountain_group_id' => $mountainGroup->id,
                    'region_id' => $region->id
                ]);
        }
    }

    protected function associateEcPoisToRegion($region)
    {
        try {
            $poisToUpdate = DB::table('ec_pois')
                ->select('ec_pois.id')
                ->whereRaw(
                    'ST_Within(ec_pois.geometry::geometry, ST_SetSRID(?::geometry, 4326))',
                    [$region->geometry]
                )
                ->get();
        } catch (\Exception $e) {
            throw $e;
            return;
        }

        foreach ($poisToUpdate as $poi) {
            DB::table('ec_pois')
                ->where('id', $poi->id)
                ->update(['region_id' => $region->id]);
        }
    }

    protected function associateHutsToRegion($region)
    {
        try {
            $hutsToUpdate = DB::table('cai_huts')
                ->select('cai_huts.id')
                ->whereRaw(
                    'ST_Within(cai_huts.geometry::geometry, ST_SetSRID(?::geometry, 4326))',
                    [$region->geometry]
                )
                ->get();
        } catch (\Exception $e) {
            throw $e;
            return;
        }

        foreach ($hutsToUpdate as $hut) {
            DB::table('cai_huts')
                ->where('id', $hut->id)
                ->update(['region_id' => $region->id]);
        }
    }

    protected function associateAllToRegion($region)
    {
        $this->associateMountainGroupsToRegion($region);
        $this->associateEcPoisToRegion($region);
    }
}
