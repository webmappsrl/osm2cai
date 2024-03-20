<?php

namespace App\Nova\Dashboards;

use Ericlagarda\NovaTextCard\TextCard;
use Laravel\Nova\Dashboard;

class AcquaSorgente extends Dashboard
{

    public static function label()
    {
        return 'Riepilogo Acqua Sorgente';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        //get all the ugcPoi with form_id = 'water'
        $ugcPoiWaterCount = \App\Models\UgcPoi::where('form_id', 'water')->count();
        return [
            (new TextCard())->width('1/4')->text('Numero di Poi Acqua Sorgente')->heading($ugcPoiWaterCount),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'acqua-sorgente';
    }
}
