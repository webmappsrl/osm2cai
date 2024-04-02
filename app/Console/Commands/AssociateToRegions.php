<?php

namespace App\Console\Commands;

use App\Models\Region;
use App\Models\MountainGroups;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\SchemaOrg\HinduTemple;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class AssociateToRegions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:associate-to-regions {--all}';
    protected $isFromNova;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate the specified model to regions based on geometric data';

    public function __construct(bool $isFromNova = false)
    {
        parent::__construct();
        $this->isFromNova = $isFromNova;
    }


    public function handle()
    {
        $this->isFromNova ? $associateAll = true : $associateAll = $this->option('all');

        //create an array with all the regions name taken from the database and add the option "all"
        $regions = Region::all();
        $regionNames = $regions->pluck('name')->toArray();
        array_unshift($regionNames, 'all');
        $resource = $this->isFromNova || $associateAll ? 'all' : $this->choice('Which resource do you want to associate to regions?', ['mountain_groups', 'ec_pois', 'huts', 'all'], 'all');
        $regionName = $this->isFromNova || $associateAll ? 'all' : $this->choice('Which region do you want to associate the resource to?', $regionNames, 'all');

        if ($regionName === 'all') {
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

                //calculate the aggregated data for the region to fill the mitur-abruzzo dashboard
                $this->calculateAggregatedData($region);
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

                //calculate the aggregated data for the region to fill the mitur-abruzzo dashboard
                $this->calculateAggregatedData($region);
            } else {
                throw new \Exception("Region not found: {$regionName}");
            }
        }
        //call the command to fill aggregated_data in the mountain_groups table for mitur abruzzo dashboard
        Artisan::call('osm2cai:associate-to-mountain-groups');
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
            //first delete all duplicated records for the current mountain group and region
            while (count(DB::table('mountain_groups_region')->where('mountain_group_id', $mountainGroup->id)->where('region_id', $region->id)->get()) > 1) {
                DB::table('mountain_groups_region')
                    ->where('mountain_group_id', $mountainGroup->id)
                    ->where('region_id', $region->id)
                    ->limit(1)
                    ->delete();
            }
            //then insert the record if it does not exist

            if (DB::table('mountain_groups_region')->where('mountain_group_id', $mountainGroup->id)->count() < 1) {
                DB::table(('mountain_groups_region'))
                    ->insert([
                        'mountain_group_id' => $mountainGroup->id,
                        'region_id' => $region->id
                    ]);
            }
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
        $this->associateHutsToRegion($region);
    }

    /**
     * Calculate the aggregated data for the region to fill the mitur-abruzzo dashboard
     * @param Region $region
     * @return void
     */
    protected function calculateAggregatedData($region)
    {
        $mountainGroupCount = DB::table('mountain_groups_region')
            ->where('region_id', $region->id)
            ->count();
        $ecPoisCount = DB::table('ec_pois')
            ->where('region_id', $region->id)
            ->count();
        $hikingRoutesCount = DB::table('hiking_routes')
            ->join('hiking_route_region', 'hiking_routes.id', '=', 'hiking_route_region.hiking_route_id')
            ->where('hiking_route_region.region_id', $region->id)
            ->where('hiking_routes.osm2cai_status', 4)
            ->count();
        $sectionsCount = DB::table('sections')
            ->where('region_id', $region->id)
            ->count();
        $caiHutsCount = DB::table('cai_huts')
            ->where('region_id', $region->id)
            ->count();

        $aggregatedData = [
            'mountain_groups_count' => $mountainGroupCount,
            'ec_pois_count' => $ecPoisCount,
            'hiking_routes_count' => $hikingRoutesCount,
            'poi_total' => $ecPoisCount + $hikingRoutesCount,
            'sections_count' => $sectionsCount,
            'cai_huts_count' => $caiHutsCount
        ];

        $region->aggregated_data = $aggregatedData;
        $region->save();
    }
}
