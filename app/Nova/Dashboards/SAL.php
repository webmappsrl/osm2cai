<?php

namespace App\Nova\Dashboards;

use DB;
use Laravel\Nova\Card;
use Laravel\Nova\Dashboard;
use Ericlagarda\NovaTextCard\TextCard;

class SAL extends Dashboard
{

    public static function label()
    {
        return 'SAL';
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
                'regions.name',
                DB::raw('(SELECT COUNT(DISTINCT mountain_group_id) FROM mountain_groups_region WHERE region_id = regions.id) as mountain_groups_count'),
                DB::raw('COUNT(DISTINCT ec_pois.id) as ec_pois_count'),
                DB::raw('(SELECT COUNT(DISTINCT hiking_route_id) FROM hiking_route_region WHERE region_id = regions.id) as hiking_routes_count'),
                DB::raw('(COUNT(DISTINCT ec_pois.id) + (SELECT COUNT(DISTINCT hiking_route_id) FROM hiking_route_region WHERE region_id = regions.id)) as poi_total'),
                DB::raw('COUNT(DISTINCT sections.id) as sections_count'),
            ])
            ->leftJoin('ec_pois', 'regions.id', '=', 'ec_pois.region_id')
            ->leftJoin('sections', 'regions.id', '=', 'sections.region_id')
            ->groupBy('regions.id')
            ->get();

        // Create the HTML string
        $html = '<table class="table-auto w-full mt-5 ">
    <thead>
        <tr>
            <th class="px-4 py-2">Regioni</th>
            <th class="px-4 py-2">Gruppi Montuosi</th>
            <th class="px-4 py-2">POI Generico</th>
            <th class="px-4 py-2">POI Rifugio</th>
            <th class="px-4 py-2">Percorsi</th>
            <th class="px-4 py-2">POI Totali</th>
            <th class="px-4 py-2">Attivitá o Esperienze</th>
        </tr>
    </thead>
    <tbody>';

        $sumMountainGroups = 0;
        $sumEcPois = 0;
        $sumHikingRoutes = 0;
        $sumPoiTotal = 0;
        $sumSections = 0;

        foreach ($regions as $region) {
            $sumMountainGroups += $region->mountain_groups_count;
            $sumEcPois += $region->ec_pois_count;
            $sumHikingRoutes += $region->hiking_routes_count;
            $sumPoiTotal += $region->poi_total;
            $sumSections += $region->sections_count;

            $html .= "<tr class='border-b'>
        <td class='px-4 py-2'>{$region->name}</td>
        <td class='px-4 py-2'>{$region->mountain_groups_count}</td>
        <td class='px-4 py-2'>{$region->ec_pois_count}</td>
        <td class='px-4 py-2'></td>
        <td class='px-4 py-2'>{$region->hiking_routes_count}</td>
        <td class='px-4 py-2'>{$region->poi_total}</td>
        <td class='px-4 py-2'>{$region->sections_count}</td>
    </tr>";
        }

        $html .= "<tr class='border-t'>
    <td class='px-4 py-2 font-bold'>Total</td>
    <td class='px-4 py-2'>{$sumMountainGroups}</td>
    <td class='px-4 py-2'>{$sumEcPois}</td>
    <td class='px-4 py-2'></td>
    <td class='px-4 py-2'>{$sumHikingRoutes}</td>
    <td class='px-4 py-2'>{$sumPoiTotal}</td>
    <td class='px-4 py-2'>{$sumSections}</td>
</tr>";

        $html .= '</tbody></table>';

        return [
            (new TextCard())
                ->forceFullWidth()
                ->height(850)
                ->heading('SAL')
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
        return 's-a-l';
    }
}