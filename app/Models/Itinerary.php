<?php

namespace App\Models;

use App\Models\HikingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Itinerary extends Model
{
    use HasFactory;

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoute::class);
    }

    /**
     * Determines next and previous stage of each track inside the layer
     *
     * @return array
     */
    public function generateItineraryEdges(): ?array
    {
        $hikingRoutes = $this->hikingRoutes;
        $hikingRoutesIds = $hikingRoutes->pluck('id')->toArray();

        if (empty($hikingRoutes)) {
            return null;
        }

        $edges = [];

        foreach ($hikingRoutes as $hikingRoute) {

            $geometry = $hikingRoute->geometry;
            //check if geometry is type linestring or multilinestring
            $geometry = DB::select("SELECT ST_AsText(ST_SetSRID(ST_Force2D(ST_MakeLine(ARRAY(SELECT (ST_Dump(ST_Collect('" . $geometry . "'::geometry))).geom))), 4326)) As wkt")[0]->wkt;
            $geometryType = DB::select("SELECT ST_GeometryType(ST_SetSRID(ST_Force2D(ST_MakeLine(ARRAY(SELECT (ST_Dump(ST_Collect('" . $geometry . "'::geometry))).geom))), 4326)) As type")[0]->type;
            if ($geometryType == 'ST_MultiLineString') {
                $geometry = DB::select("SELECT ST_AsText(ST_SetSRID(ST_Force2D(ST_LineMerge('" . $geometry . "')), 4326)) As wkt")[0]->wkt;
            }

            $start_point = DB::select("SELECT ST_AsText(ST_SetSRID(ST_Force2D(ST_StartPoint('" . $geometry . "')), 4326)) As wkt")[0]->wkt;
            $end_point = DB::select("SELECT ST_AsText(ST_SetSRID(ST_Force2D(ST_EndPoint('" . $geometry . "')), 4326)) As wkt")[0]->wkt;

            $nextHikingRoute = HikingRoute::whereIn('id', $hikingRoutesIds)
                ->where('id', '<>', $hikingRoute->id)
                ->whereRaw("ST_DWithin(ST_SetSRID(geometry, 4326), ST_GeomFromText('{$end_point}', 4326), 0.001)")
                ->get();

            $previousHikingRoute = HikingRoute::whereIn('id', $hikingRoutesIds)
                ->where('id', '<>', $hikingRoute->id)
                ->whereRaw("ST_DWithin(ST_SetSRID(geometry, 4326), ST_GeomFromText('{$start_point}', 4326), 0.001)")
                ->get();

            $edges[$hikingRoute->id]['prev'] = $previousHikingRoute->pluck('id')->toArray();
            $edges[$hikingRoute->id]['next'] = $nextHikingRoute->pluck('id')->toArray();
        }
        return $edges;
    }
}
