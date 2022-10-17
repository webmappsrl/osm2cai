<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use App\Services\CardsService;
use App\Providers\NovaServiceProvider;

class SectorsDashboard extends Dashboard
{


    public static function label()
    {
        return 'Riepilogo settori';
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        $cardsService = new CardsService;
        $cards = $cardsService->getSectorsTableCard();
        return [
            $cards
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'sectors-dashboard';
    }
}
