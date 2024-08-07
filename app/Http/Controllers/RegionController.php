<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class RegionController extends Controller
{
    public function geojson(string $id)
    {
        $region = Region::find($id);
        $sectors = $region->sectorsIds();
        $results = Sector::whereIn('id', $sectors)->select('id', DB::raw('ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom'))->get();
        if (count($results) > 0) {
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
                'properties' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'code' => $region->code,
                    'full_code' => $region->code,
                    'geojson_url' => route('api.geojson.region', ['id' => $region->id]),
                    'shapefile_url' => route('api.shapefile.region', ['id' => $region->id]),
                    'kml' => route('api.kml.region', ['id' => $region->id]),
                ]
            ];

            foreach ($results as $result) {
                $sector = Sector::find($result->id);
                $geojson['features'][] =
                    [
                        'type' => 'Feature',
                        'geometry' => json_decode($result->geom),
                        'properties' => [
                            'id' => $sector->id,
                            'name' => $sector->name,
                            'code' => $sector->code,
                            'full_code' => $sector->full_code,
                            'area' => $sector->area->name,
                            'province' => $sector->area->province->name,
                            'region' => $sector->area->province->region->name,
                            'geojson_url' => route('api.geojson.sector', ['id' => $sector->id]),
                            'shapefile_url' => route('api.shapefile.sector', ['id' => $sector->id]),
                            'kml' => route('api.kml.sector', ['id' => $sector->id]),
                        ]
                    ];
            }

            $headers = [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $id . '.geojson"',
            ];

            return response(json_encode($geojson), 200, $headers);
        } else
            return response()->json(['Error' => 'Region ' . $id . ' not found'], 404);
    }

    public function shapefile(string $id)
    {
        $model = Region::find($id);
        $name = str_replace(" ", "_", $model->name);
        $shapefile = $model->getShapefile();

        return Storage::disk('public')->download($shapefile, $name . '.zip');
    }

    public function hikingRouteShapefile(string $id)
    {
        $model = Region::find($id);
        $name = 'osm2cai_hikingroutes_' . str_replace(" ", "_", $model->name);
        $shapefile = $model->getHikingRoutesShapefile();

        return Storage::disk('public')->download($shapefile, $name . '.zip');
    }

    public function kml(string $id)
    {
        $region = Region::find($id);

        $headers = [
            'Content-type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $id . '.kml"',
        ];

        return response($region->getKml(), 200, $headers);
    }

    public function csv(string $id)
    {
        $region = Region::find($id);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_regione_' . $region->name . '.csv"',
        ];

        return response($region->getCsv(), 200, $headers);
    }

    public function geojsonComplete(string $id)
    {
        $region = Region::find($id);

        $headers = [
            'Content-type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_regione_complete_' . $region->name . '.geojson"',
        ];

        return response($region->getGeojsonComplete(), 200, $headers);
    }
}
