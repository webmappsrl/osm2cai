<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Ericlagarda\NovaTextCard\TextCard;

class Utenti extends Dashboard
{

    public static function label()
    {
        return 'Riepilogo utenti';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        $users = Cache::remember('users', 60, function () {
            return \App\Models\User::all();
        });

        $mostActiveUsers = Cache::remember('mostActiveUsers', 60, function () {
            return DB::select(DB::raw("
            SELECT u.id AS user_id, u.name AS user_name, COUNT(DISTINCT hr.id) AS numero_validazioni
            FROM users u
            JOIN hiking_routes hr ON u.id = hr.user_id
            WHERE hr.osm2cai_status = '4'
            GROUP BY u.id, u.name
            ORDER BY numero_validazioni DESC
            LIMIT 5
        "));
        });

        $html = '<ol style="margin-top:10px;">';
        foreach ($mostActiveUsers as $user) {
            $url = url('/resources/users/' . $user->user_id);
            $html .= "<li><a style='text-decoration:none; color: darkgreen;' href=\"$url\">$user->user_name</a> | N. Validazioni: <strong>$user->numero_validazioni</strong></li>";
        }
        $html .= '</ol>';
        return [
            new \App\Nova\Metrics\TotalUsers,
            new \App\Nova\Metrics\UserDistributionByRole($users),
            new \App\Nova\Metrics\UserDistributionByRegion($users),
            (new TextCard())
                ->center(false)
                ->heading('Most Active Users')
                ->text($html)
                ->textAsHtml()
                ->height(),
        ];
    }

    /**
     * Get the URI key for the dashboard.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'utenti';
    }
}
