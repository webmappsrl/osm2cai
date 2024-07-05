<?php

namespace App\Http\Controllers;

use App\Models\EcPoi;
use App\Models\CaiHuts;
use Illuminate\Http\Request;
use App\Models\MountainGroups;
use Illuminate\Support\Facades\DB;

class MiturAbruzzoMapsController extends Controller
{
    //TODO IMPLEMENT THE CORRECT MAP LINKS      
    public static function poiMap($id)
    {
        $poi = EcPoi::findOrFail($id);

        $geometry = DB::select("SELECT ST_AsText(geometry) AS geometry FROM ec_pois WHERE id = ?", [$id]);
        if (!$geometry) {
            return redirect('https://26.app.geohub.webmapp.it/#/map');
        }
        $geometry = str_replace(['POINT(', ')'], '', $geometry[0]->geometry);
        list($longitude, $latitude) = explode(' ', $geometry);

        return view('maps.poi', ['poi' => $poi, 'latitude' => $latitude, 'longitude' => $longitude]);
    }
    public static function mountainGroupsMap($id)
    {
        $mountainGroup = MountainGroups::findOrFail($id);
        // get the geometry in geojson format
        $geometry = DB::select("SELECT ST_AsGeoJSON(geometry) as geom FROM mountain_groups WHERE id = ?", [$id]);
        if (!$geometry) {
            return redirect('https://26.app.geohub.webmapp.it/#/map');
        }

        $geometry = $geometry[0]->geom; // This is now a JSON string

        return view('maps.mountain-group', [
            'mountainGroup' => $mountainGroup,
            'geometry' => $geometry
        ]);
    }

    public static function caiHutsMap($id)
    {
        $caiHut = CaiHuts::findOrFail($id);

        $geometry = DB::select("SELECT ST_AsGeoJSON(geometry) as geom FROM cai_huts WHERE id = ?", [$id]);
        if (!$geometry) {
            return redirect('https://26.app.geohub.webmapp.it/#/map');
        }
        $geometry = json_decode($geometry[0]->geom, true);

        return view('maps.cai-hut', [
            'caiHut' => $caiHut,
            'geometry' => $geometry,
        ]);
    }


    public static function mountainGroupsHrMap($id)
    {
        $mountainGroup = MountainGroups::findOrFail($id);

        // Recuperare la geometria del gruppo montuoso
        $geometry = DB::select("SELECT ST_AsGeoJSON(geometry) as geom FROM mountain_groups WHERE id = ?", [$id]);
        if (!$geometry) {
            return redirect('https://26.app.geohub.webmapp.it/#/map');
        }
        $geometry = json_decode($geometry[0]->geom, true); // Convertiamo in array associativo

        // Recuperare gli ID delle hiking routes intersecanti
        $hikingRoutesIntersectingIds = array_keys(json_decode($mountainGroup->hiking_routes_intersecting, true));

        // Recuperare le geometrie delle hiking routes intersecanti
        $hikingRoutesGeojson = array_map(function ($hikingRoute) {
            $routeGeom = DB::select("SELECT ST_AsGeoJSON(geometry) as geom FROM hiking_routes WHERE id = ?", [$hikingRoute]);
            return json_decode($routeGeom[0]->geom, true); // Convertiamo in array associativo
        }, $hikingRoutesIntersectingIds);

        return view('maps.mountain-group-hr', [
            'mountainGroup' => $mountainGroup,
            'geometry' => json_encode($geometry), // Convertiamo in JSON string
            'hikingRoutesGeojson' => json_encode($hikingRoutesGeojson) // Convertiamo in JSON string
        ]);
    }
}
