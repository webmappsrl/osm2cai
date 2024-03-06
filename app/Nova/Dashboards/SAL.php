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
        $regions = DB::table('regions')->get();

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
            $data = json_decode($region->aggregated_data);

            $html .= "<tr class='border-b'>
        <td class='px-4 py-2'>{$region->name}</td>
        <td class='px-4 py-2'>{$data->mountain_groups_count}</td>
        <td class='px-4 py-2'>{$data->ec_pois_count}</td>
        <td class='px-4 py-2'>{$data->cai_huts_count}</td>
        <td class='px-4 py-2'>{$data->hiking_routes_count}</td>
        <td class='px-4 py-2'>{$data->poi_total}</td>
        <td class='px-4 py-2'>{$data->sections_count}</td>
    </tr>";

            $sumMountainGroups += $data->mountain_groups_count;
            $sumEcPois += $data->ec_pois_count;
            $sumHikingRoutes += $data->hiking_routes_count;
            $sumPoiTotal += $data->poi_total;
            $sumSections += $data->sections_count;
            $sumCaiHuts += $data->cai_huts_count;
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
