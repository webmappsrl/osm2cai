<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PoiMapController extends Controller
{
    public static function poiMap($id)
    {
        return redirect('https://26.app.geohub.webmapp.it/#/map');
    }
}