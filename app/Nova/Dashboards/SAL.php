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
        return 'Riepilogo MITUR-Abruzzo';
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
                DB::raw('(SELECT COUNT(DISTINCT hr.id) FROM (SELECT hiking_route_id as id FROM hiking_route_region WHERE region_id = regions.id) as hr JOIN hiking_routes ON hr.id = hiking_routes.id WHERE hiking_routes.osm2cai_status = 4) as hiking_routes_count'),
                DB::raw('(COUNT(DISTINCT ec_pois.id) + (SELECT COUNT(DISTINCT hr.id) FROM (SELECT hiking_route_id as id FROM hiking_route_region WHERE region_id = regions.id) as hr JOIN hiking_routes ON hr.id = hiking_routes.id WHERE hiking_routes.osm2cai_status = 4)) as poi_total'),
                DB::raw('COUNT(DISTINCT sections.id) as sections_count'),
                DB::raw('COUNT(DISTINCT cai_huts.id) as cai_huts_count'), // Aggiungi questa riga
            ])
            ->leftJoin('ec_pois', 'regions.id', '=', 'ec_pois.region_id')
            ->leftJoin('sections', 'regions.id', '=', 'sections.region_id')
            ->leftJoin('cai_huts', 'regions.id', '=', 'cai_huts.region_id') // Aggiungi questa join
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
        $sumCaiHuts = 0;

        foreach ($regions as $region) {
            $sumMountainGroups += $region->mountain_groups_count;
            $sumEcPois += $region->ec_pois_count;
            $sumHikingRoutes += $region->hiking_routes_count;
            $sumPoiTotal += $region->poi_total;
            $sumSections += $region->sections_count;
            $sumCaiHuts += $region->cai_huts_count;

            $html .= "<tr class='border-b'>
        <td class='px-4 py-2'>{$region->name}</td>
        <td class='px-4 py-2'>{$region->mountain_groups_count}</td>
        <td class='px-4 py-2'>{$region->ec_pois_count}</td>
        <td class='px-4 py-2'> {$region->cai_huts_count}</td>
        <td class='px-4 py-2'>{$region->hiking_routes_count}</td>
        <td class='px-4 py-2'>{$region->poi_total}</td>
        <td class='px-4 py-2'>{$region->sections_count}</td>
    </tr>";
        }

        $html .= "<tr class='border-t'>
    <td class='px-4 py-2 font-bold'>Total</td>
    <td class='px-4 py-2'>{$sumMountainGroups}</td>
    <td class='px-4 py-2'>{$sumEcPois}</td>
    <td class='px-4 py-2'> {$sumCaiHuts}</td>
    <td class='px-4 py-2'>{$sumHikingRoutes}</td>
    <td class='px-4 py-2'>{$sumPoiTotal}</td>
    <td class='px-4 py-2'>{$sumSections}</td>
</tr>";

        $html .= '</tbody></table>';

        return [
            (new TextCard())
                ->forceFullWidth()
                ->height(850)
                ->heading('MITUR-Abruzzo')
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
        return 'mitur-abruzzo';
    }
}
