<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Illuminate\Support\Facades\DB;
use Ericlagarda\NovaTextCard\TextCard;

class PercorsiFavoriti extends Dashboard
{
    public static function label()
    {
        return 'Percorsi Favoriti';
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        $regions = DB::table('regions')
            ->select([
                'regions.name as region_name',
                DB::raw('(SELECT COUNT(*) FROM hiking_route_region hrr JOIN hiking_routes hr ON hrr.hiking_route_id = hr.id WHERE hrr.region_id = regions.id AND hr.region_favorite = true) as favorite_routes_count'),
                DB::raw('(SELECT COUNT(*) FROM hiking_route_region hrr JOIN hiking_routes hr ON hrr.hiking_route_id = hr.id WHERE hrr.region_id = regions.id AND hr.osm2cai_status = 4) as sda4_routes_count'),
            ])
            ->get();

        $html = '<table class="table-auto w-full mt-5">
    <thead>
        <tr>
            <th class="px-4 py-2">Regioni</th>
            <th class="px-4 py-2">Percorsi Favoriti</th>
            <th class="px-4 py-2">Percorsi SDA4</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($regions as $region) {
            $html .= "<tr class='border-b'>
        <td class='px-4 py-2'>{$region->region_name}</td>
        <td class='px-4 py-2'>{$region->favorite_routes_count}</td>
        <td class='px-4 py-2'>{$region->sda4_routes_count}</td>
    </tr>";
        }

        $html .= '</tbody></table>';

        return [
            (new TextCard())
                ->forceFullWidth()
                ->height('auto') // Adegua l'altezza in base al contenuto
                ->heading('Percorsi Favoriti')
                ->text($html)
                ->textAsHtml()
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'percorsi-favoriti';
    }
}
