<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class Osm2CaiHikingRoutesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Osm2CaiHikingRoutesServiceProvider::class, function ($app) {
            return new Osm2CaiHikingRoutesServiceProvider($app);
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    private function getWhereByCode($code) {
        $len = strlen($code);
        switch ($len) {
            case 1:
                // L : Regione
                $where = "regione_codice_cai='$code'";
                break;
            case 3:
                // LPI : Provincia
                $reg=$code[0];
                $prov=$code[1].$code[2];
                $where = "regione_codice_cai='$reg' AND 
                          provincia_sigla='$prov'
                          ";
                break;
            case 4:
                // LPIO : Area
                $reg=$code[0];
                $prov=$code[1].$code[2];
                $area=$code[3];
                $where = "regione_codice_cai='$reg' AND 
                          provincia_sigla='$prov' AND 
                          area_codice='$area' 
                          ";
                break;
            case 5:
                // LPIO1 : Settore
                $reg=$code[0];
                $prov=$code[1].$code[2];
                $area=$code[3];
                $sect=$code[4];
                $where = "regione_codice_cai='$reg' AND 
                          provincia_sigla='$prov' AND 
                          area_codice='$area' AND 
                          settore_codice='$sect'
                          ";
                break;
            default:
                return '';
        }
        return $where;

    }

    public function checkCode($code) : bool {
        // Step 0. Check Syntax
        if(strlen($code)>5) return false;
        if(strlen($code)==2) return false;

        // Step 1. Build query
        $where = $this->getWhereByCode($code);
        // Step 2. Perform query
        $caiDb = DB::connection("pgsql_cai");
        $num = $caiDb->table("aree_settori")->whereRaw($where)->count();

        // Step 3. Check if elements are there
        if($num > 0) return true;
        return false;
    }

    /**
     * SELECT DISTINCT relation_id,ref FROM hiking_routes AS r1, aree_settori AS s1
    WHERE regione_codice_cai='L' AND provincia_sigla='PI' AND area_codice='O' AND settore_codice='1'
    AND ST_Intersects (r1.geom,s1.geom);
     */
    public function getHikingRoutes($code) : array {
        $routes=[];
        $caiDb = DB::connection("pgsql_cai");
        $where = $this->getWhereByCode($code);
        $select = "SELECT DISTINCT relation_id,ref FROM hiking_routes AS r1, aree_settori AS s1 WHERE $where AND ST_Intersects (r1.geom,s1.geom);";
        $routes = $caiDb->select($select);
        return $routes;
    }

    public function getAllRoutes() : array {
        $caiDb = DB::connection("pgsql_cai");
        $select = "SELECT DISTINCT relation_id,ref FROM hiking_routes;";
        $routes = $caiDb->select($select);
        return $routes;
    }

    public function getHikingRoute($osmid) {

    }
}
