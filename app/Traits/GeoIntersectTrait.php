<?php

namespace App\Traits;

use App\Models\EcPoi;
use App\Models\CaiHuts;
use App\Models\HikingRoute;
use App\Models\MountainGroups;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait GeoIntersectTrait
{

    /**
     * Get hiking routes that intersect with the given model
     * 
     * @return Collection
     */
    public function getHikingRoutesIntersecting(): Collection
    {
        $model = $this;

        $intersectingHikingRouteIds = DB::table('hiking_routes')
            ->select('id')
            ->whereRaw("ST_Intersects(geometry, (SELECT geometry FROM " . $model->getTable() . " WHERE id = ?))", [$model->id])
            ->pluck('id');


        return HikingRoute::whereIn('id', $intersectingHikingRouteIds)->get();
    }

    /**
     * Get huts that intersect with the given model
     * 
     * @return Collection
     */
    public function getHutsIntersecting(): Collection
    {
        $model = $this;

        $intersectingHutsIds = DB::table('cai_huts')
            ->select('id')
            ->whereRaw("ST_Intersects(geometry, (SELECT geometry FROM " . $model->getTable() . " WHERE id = ?))", [$model->id])
            ->pluck('id');

        return CaiHuts::whereIn('id', $intersectingHutsIds)->get();
    }

    /**
     * Get pois that intersect with the given model
     * 
     * @return Collection
     */
    public function getPoisIntersecting(): Collection
    {
        $model = $this;
        $intersectingPoisIds = DB::table('ec_pois')
            ->select('id')
            ->whereRaw("ST_Intersects(geometry, (SELECT geometry FROM " . $model->getTable() . " WHERE id = ?))", [$model->id])
            ->pluck('id');

        return EcPoi::whereIn('id', $intersectingPoisIds)->get();
    }

    /**
     * Get Mountain groups that intersect with the given model
     * 
     * @return Collection
     */
    public function getMountainGroupsIntersecting(): Collection
    {
        $model = $this;
        $intersectingMountainGroupsIds = DB::table('mountain_groups')
            ->select('id')
            ->whereRaw("ST_Intersects(geometry, (SELECT geometry FROM " . $model->getTable() . " WHERE id = ?))", [$model->id])
            ->pluck('id');

        return MountainGroups::whereIn('id', $intersectingMountainGroupsIds)->get();
    }

    /**
     * Get the geometry of the given model
     * 
     * @return array
     */

    public function getGeometry(): array
    {
        $model = $this;
        $geom_s = $model
            ->select(
                DB::raw("ST_AsGeoJSON(geometry) as geom")
            )
            ->first()
            ->geom;
        $geom = json_decode($geom_s, TRUE);

        return $geom;
    }
}
