<?php

namespace App\Nova\Metrics;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class UserDistributionByRole extends Partition
{

    protected $users;

    public function __construct(iterable $users)
    {
        $this->users = $users;
    }
    /**
     * Calculate the value of the metric.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $keys = ['Superadmin', 'Referente Nazionale', 'Referente Regionale', 'Referente di Zona', 'Sconosciuto'];
        $adminUsers = 0;
        $nationalUsers = 0;
        $regionalUsers = 0;
        $localUsers = 0;
        $unknownUsers = 0;

        foreach ($this->users as $user) {
            $permission = $user->getPermissionString();

            switch ($permission) {
                case 'Superadmin':
                    $adminUsers++;
                    break;
                case 'Referente nazionale':
                    $nationalUsers++;
                    break;
                case 'Referente regionale':
                    $regionalUsers++;
                    break;
                case 'Referente di zona':
                    $localUsers++;
                    break;
                default:
                    $unknownUsers++;
            }
        }
        $result = array_combine($keys, [$adminUsers, $nationalUsers, $regionalUsers, $localUsers, $unknownUsers]);
        return $this->result($result);
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'user-distribution-by-role';
    }
}
