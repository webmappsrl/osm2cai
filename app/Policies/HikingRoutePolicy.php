<?php

namespace App\Policies;

use App\Models\User;
use AWS\CRT\HTTP\Request;
use App\Models\HikingRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class HikingRoutePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, HikingRoute $route)
    {
        return true;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, HikingRoute $route)
    {
        // $permission = $user->getPermissionString();
        // if ($permission == 'Superadmin' || $permission == 'Referente nazionale') {
        //     return true;
        // }

        // if ($permission == 'Referente regionale') {
        //     if (!$route->regions->intersect($user->regions)->isEmpty()) {
        //         return true;
        //     }
        //     return false;
        // }

        // if ($permission == 'Referente di zona') {
        //     if (!$route->sectors->intersect($user->sectors)->isEmpty()) {
        //         return true;
        //     } else if (!$route->areas->intersect($user->areas)->isEmpty()) {
        //         return true;
        //     } else if (!$route->provinces->intersect($user->provinces)->isEmpty()) {
        //         return true;
        //     } else {
        //         return false;
        //     }
        // }

        return true;
    }

    public function delete(User $user, HikingRoute $route)
    {
        return true;
    }

    /**
     * Determine whether the user can attach any hikingRoute to the Section.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HikingRoute  $hikingRoute
     * @return mixed
     */
    public function attachAnySection(User $user, HikingRoute $hikingRoute)
    {
        return false;
    }
}
