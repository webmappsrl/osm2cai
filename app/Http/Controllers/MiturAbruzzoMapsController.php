<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MiturAbruzzoMapsController extends Controller
{
    //TODO IMPLEMENT THE CORRECT MAP LINKS      
    public static function poiMap($id)
    {
        return redirect('https://26.app.geohub.webmapp.it/#/map');
    }

    public static function mountainGroupsMap($id)
    {
        return redirect('https://26.app.geohub.webmapp.it/#/map');
    }

    public static function caiHutsMap($id)
    {
        return redirect('https://26.app.geohub.webmapp.it/#/map');
    }
}