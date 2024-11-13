<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\HikingRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SectorController extends Controller
{
    public function geojson(string $id)
    {
        $sector = Sector::find($id);
        $results = DB::select('SELECT ST_AsGeoJSON(ST_ForceRHR(geometry)) as geom FROM sectors WHERE id = ?;', [$id]);
        if (count($results) > 0) {
            $geojson = [
                'type' => 'Feature',
                'geometry' => json_decode($results[0]->geom),
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

            $headers = [
                'Content-type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $id . '.geojson"',
            ];

            return response(json_encode($geojson), 200, $headers);
        } else
            return response()->json(['Error' => 'Sector ' . $id . ' not found'], 404);
    }

    public function geojsonComplete(string $id)
    {
        $sector = Sector::find($id);

        $headers = [
            'Content-type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_settore_complete_' . $sector->name . '.geojson"',
        ];

        return response($sector->getGeojsonComplete(), 200, $headers);
    }

    public function shapefile(string $id)
    {
        $model = Sector::find($id);
        $name = str_replace(" ", "_", $model->name);
        $shapefile = $model->getShapefile();

        return Storage::disk('public')->download($shapefile, $name . '.zip');
    }

    public function kml(string $id)
    {
        $sector = Sector::find($id);

        $headers = [
            'Content-type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $id . '.kml"',
        ];

        return response($sector->getKml(), 200, $headers);
    }

    public function csv(string $id)
    {
        $sector = Sector::find($id);

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="osm2cai_' . date('Ymd') . '_settore_' . $sector->name . '.csv"',
        ];

        return response($sector->getCsv(), 200, $headers);
    }
}
