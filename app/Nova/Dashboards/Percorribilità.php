<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;

class Percorribilità extends Dashboard
{

    public static function label()
    {
        return 'Percorribilità';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        $hikingRoutesSda4 = \App\Models\HikingRoute::select('issues_status')->where('osm2cai_status', 4)->get();
        $hikingRoutesSda34 = \App\Models\HikingRoute::select('issues_status')->whereIn('osm2cai_status', [3, 4])->get();
        return [
            new \App\Nova\Metrics\Sda4IssueStatusPartition($hikingRoutesSda4),
            new \App\Nova\Metrics\Sda3And4IssueStatusPartition($hikingRoutesSda34)
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'percorribilità';
    }
}
