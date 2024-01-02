<?php

namespace App\Nova\Dashboards;

use App\Models\User;
use Laravel\Nova\Dashboard;
use Illuminate\Support\Facades\Cache;

class Percorribilità extends Dashboard
{

    protected $user;

    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    public static function label()
    {
        return 'Riepilogo Percorribilità';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        if ($this->user) {
            $region = $this->user->region;
            $hikingRoutesSda4 = Cache::remember('hikingRoutesSda4', 60, function () use ($region) {
                return \App\Models\HikingRoute::select('issues_status')
                    ->where('osm2cai_status', 4)
                    ->whereHas('regions', function ($query) use ($region) {
                        $query->where('regions.id', $region->id);
                    })
                    ->get();
            });
            $hikingRoutesSda34 = Cache::remember('hikingRoutesSda34', 60, function () use ($region) {
                return \App\Models\HikingRoute::select('issues_status')
                    ->whereIn('osm2cai_status', [3, 4])
                    ->whereHas('regions', function ($query) use ($region) {
                        $query->where('regions.id', $region->id);
                    })
                    ->get();
            });
        } else {
            $hikingRoutesSda4 = Cache::remember('hikingRoutesSda4', 60, function () {
                return \App\Models\HikingRoute::select('issues_status')
                    ->where('osm2cai_status', 4)
                    ->get();
            });
            $hikingRoutesSda34 = Cache::remember('hikingRoutesSda34', 60, function () {
                return \App\Models\HikingRoute::select('issues_status')
                    ->whereIn('osm2cai_status', [3, 4])
                    ->get();
            });
        }
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
