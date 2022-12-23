<?php

namespace App\Http\Controllers;

use App\Models\HikingRoute;
use Illuminate\Http\Request;

class HikingRouteController extends Controller {
    public function boundingBox(Request $request) {
        // TODO: Check (bad)data and returns code
        // Retrieve data
        $geojson = HikingRoute::geojsonByBoundingBox(
            $request->input('osm2cai_status'),
            $request->input('lo0'),
            $request->input('la0'),
            $request->input('lo1'),
            $request->input('la1')
        );

        // Return
        return response($geojson, 200, ['Content-type' => 'application/json']);
    }

    public function gpx(Request $request){
        $HR = HikingRoute::where('id',$request->input('id'))->first();
        $gpx = $HR->getGPXGeometry('geometry');
        $filename = 'Traccia Validata';
        $this->downloadGPX($gpx,$filename);
    }

    public function gpx_osm(Request $request){
        $HR = HikingRoute::where('id',$request->input('id'))->first();
        $gpx = $HR->getGPXGeometry('geometry_osm');
        $filename = 'Traccia OSM';
        $this->downloadGPX($gpx,$filename);
    }

    public function gpx_raw(Request $request){
        $HR = HikingRoute::where('id',$request->input('id'))->first();
        $gpx = $HR->getGPXGeometry('geometry_raw');
        $filename = 'Traccia RAW';
        $this->downloadGPX($gpx,$filename);
    }

    private function dowloadGPX($gpx,$filename){
        
    }
}
