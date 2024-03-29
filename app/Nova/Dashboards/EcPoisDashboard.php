<?php

namespace App\Nova\Dashboards;

use Ericlagarda\NovaTextCard\TextCard;
use Laravel\Nova\Dashboard;

class EcPoisDashboard extends Dashboard
{

    public static function label()
    {
        return 'POIS';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            new \App\Nova\Metrics\EcPoisTrend,
            (new TextCard())->width('1/2')->text('POI totali')->heading(\App\Models\EcPoi::count()),
            new \App\Nova\Metrics\EcPoisScorePartition,
            new \App\Nova\Metrics\EcPoisTypePartition,
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'ec-pois';
    }
}
