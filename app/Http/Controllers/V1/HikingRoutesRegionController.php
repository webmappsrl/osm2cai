<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\HikingRoute;
use App\Models\Region;
use Illuminate\Http\Request;

class HikingRoutesRegionController extends Controller
{
    public function hikingroutelist(string $region_id,string $sda) {
        $region_id = strtoupper($region_id);
        
        $sda = explode(',',$sda);
        $list = HikingRoute::query();
        $list = HikingRoute::whereHas('regions',function($query) use ($region_id) { $query->where('code',$region_id); })->where(function ($query) use ($sda) {
            if (count($sda) == 1) {
                return $query->where('osm2cai_status', $sda[0]);
            }
            if (count($sda) == 2) {
                return $query->where('osm2cai_status', $sda[0])
                             ->orWhere('osm2cai_status', '=', $sda[1]);
            }
            if (count($sda) == 3) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2]);
            }
            if (count($sda) == 4) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3]);
            }
            if (count($sda) == 5) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3])
                            ->orWhere('osm2cai_status', '=', $sda[4]);
            }
        })
        ->get();

        // $list = $list->pluck('ref_REI_comp')->toArray();
        $list = $list->pluck('id')->toArray();

        // Return
        return response($list, 200, ['Content-type' => 'application/json']);
    }

    public function hikingrouteosmlist(string $region_id,string $sda) {
        $region_id = strtoupper($region_id);
        
        $sda = explode(',',$sda);
        $list = HikingRoute::query();
        $list = HikingRoute::whereHas('regions',function($query) use ($region_id) { $query->where('code',$region_id); })->where(function ($query) use ($sda) {
            if (count($sda) == 1) {
                return $query->where('osm2cai_status', $sda[0]);
            }
            if (count($sda) == 2) {
                return $query->where('osm2cai_status', $sda[0])
                             ->orWhere('osm2cai_status', '=', $sda[1]);
            }
            if (count($sda) == 3) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2]);
            }
            if (count($sda) == 4) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3]);
            }
            if (count($sda) == 5) {
                return $query->where('osm2cai_status', $sda[0])
                            ->orWhere('osm2cai_status', '=', $sda[1])
                            ->orWhere('osm2cai_status', '=', $sda[2])
                            ->orWhere('osm2cai_status', '=', $sda[3])
                            ->orWhere('osm2cai_status', '=', $sda[4]);
            }
        })
        ->get();

        // $list = $list->pluck('ref_REI_comp')->toArray();
        $list = $list->pluck('relation_id')->toArray();

        // Return
        return response($list, 200, ['Content-type' => 'application/json']);
    }
    
    public function hikingroutebyid(int $id) {
        
        $item = HikingRoute::where('id', $id)->get();

        // Return
        return response($item, 200, ['Content-type' => 'application/json']);
    }
    
    public function hikingroutebyosmid(int $id) {
        
        $item = HikingRoute::where('relation_id', $id)->get();

        // Return
        return response($item, 200, ['Content-type' => 'application/json']);
    }
}
