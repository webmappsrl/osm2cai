<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Card;
use Laravel\Nova\Dashboard;
use Ericlagarda\NovaTextCard\TextCard;
use Illuminate\Support\Facades\DB;

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


        //----------------------Regions table----------------------------

        $regions = DB::table('regions')->get();

        // Create the HTML string
        $html = '<div style="margin-top:50px; margin-bottom:50px; border-bottom: 1px solid black;" ><h2> Regioni </h2></div>';
        $html .= '<table class="table-auto w-full mt-5 ">
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

        $sumMountainGroups = DB::select('SELECT count(*) as count FROM mountain_groups')[0]->count;
        $sumEcPois = DB::select('SELECT count(*) as count FROM ec_pois')[0]->count;
        $sumHikingRoutes = DB::select('SELECT count(*) as count FROM hiking_routes')[0]->count;
        $sumPoiTotal = $sumEcPois + $sumHikingRoutes;
        $sumSections = DB::select('SELECT count(*) as count FROM sections')[0]->count;
        $sumCaiHuts = DB::select('SELECT count(*) as count FROM cai_huts')[0]->count;

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



        // ----------------------Mountain groups table----------------------------


        //         $mountainGroups = DB::table('mountain_groups')->get();

        //         //add a separator between the two tables
        //         $html .= '<div style="margin-top:50px; margin-bottom:50px; border-bottom: 1px solid black;" ><h2> Gruppi Montuosi </h2></div>';

        //         // Create the HTML string
        //         $html .= '<table class="table-auto w-full mt-5 ">
        //     <thead>
        //         <tr>
        //             <th class="px-4 py-2">Nome</th>
        //             <th class="px-4 py-2">POI Generico</th>
        //             <th class="px-4 py-2">POI Rifugio</th>
        //             <th class="px-4 py-2">Percorsi POI Totali</th>
        //             <th class="px-4 py-2">Attivtá o Esperienze</th>
        //         </tr>
        //     </thead>
        //     <tbody>';

        //         $sumMountainGroupsEcPois = 0;
        //         $sumMountainGroupsHikingRoutes = 0;
        //         $sumMountainGroupsPoiTotal = 0;
        //         $sumMountainGroupsSections = 0;
        //         $sumMountainGroupsHuts = 0;

        //         foreach ($mountainGroups as $mountainGroup) {
        //             $mountainGroupData = json_decode($mountainGroup->aggregated_data) ?? (object) [
        //                 'ec_pois_count' => 0,
        //                 'hiking_routes_count' => 0,
        //                 'poi_total' => 0,
        //                 'sections_count' => 0,
        //                 'cai_huts_count' => 0
        //             ];

        //             $html .= "<tr class='border-b'>
        //         <td class='px-4 py-2'>{$mountainGroup->name}</td>
        //         <td class='px-4 py-2'>{$mountainGroupData->ec_pois_count}</td>
        //         <td class='px-4 py-2'>{$mountainGroupData->cai_huts_count}</td>
        //         <td class='px-4 py-2'>{$mountainGroupData->poi_total}</td>
        //         <td class='px-4 py-2'>{$mountainGroupData->sections_count}</td>

        //     </tr>";

        //             $sumMountainGroupsEcPois += $mountainGroupData->ec_pois_count;
        //             $sumMountainGroupsHikingRoutes += $mountainGroupData->hiking_routes_count;
        //             $sumMountainGroupsPoiTotal += $mountainGroupData->poi_total;
        //             $sumMountainGroupsHuts += $mountainGroupData->cai_huts_count;
        //             $sumMountainGroupsSections += $mountainGroupData->sections_count;
        //         }

        //         $html .= "<tr class='border-t'>
        //     <td class='px-4 py-2 font-bold'>Total</td>
        //     <td class='px-4 py-2'>{$sumMountainGroupsEcPois}</td>
        //     <td class='px-4 py-2'> {$sumMountainGroupsHuts}</td>
        //     <td class='px-4 py-2'>{$sumMountainGroupsPoiTotal}</td>
        //     <td class='px-4 py-2'>{$sumMountainGroupsSections}</td>
        // </tr>";

        //         $html .= '</tbody></table>';

        return [
            (new TextCard())
                ->forceFullWidth()
                ->height('1000px')
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
