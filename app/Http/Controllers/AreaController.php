<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Sector;
use App\Nova\Province;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AreaController extends Controller
{
    public function geojson(string $id)
    {
        $area = Area::find($id);
        $sectors = $area->sectorsIds();
        $results = Sector::whereIn('id', $sectors)->select('id', DB::raw('ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom'))->get();
        if (count($results) > 0) {
            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
                'properties' => [
                    'id' => $area->id,
                    'name' => $area->name,
                    'code' => $area->code,
                    'full_code' => $area->full_code,
                    'province' => $area->province->name,
                    'region' => $area->province->region->name,
                    'geojson_url' => route('api.geojson.area', ['id' => $area->id]),
                    'shapefile_url' => route('api.shapefile.area', ['id' => $area->id]),
                    'kml' => route('api.kml.area', ['id' => $area->id]),
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
            return response()->json(['Error' => 'Area ' . $id . ' not found'], 404);
    }

    public function shapefile(string $id)
    {
        $model = Area::find($id);
        $name = str_replace(" ", "_", $model->name);
        $shapefile = $model->getShapefile();

        return Storage::disk('public')->download($shapefile, $name . '.zip');
    }

    public function kml(string $id)
    {
        $area = Area::find($id);

        $headers = [
            'Content-type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $id . '.kml"',
        ];

        return response($area->getKml(), 200, $headers);
    }

    public function csv(string $id)
    {
        $area = Area::find($id);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_area_' . $area->name . '.csv"',
        ];

        return response($area->getCsv(), 200, $headers);
    }
}
