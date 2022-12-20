<?php

namespace App\Policies;

use App\Models\HikingRoute;
use App\Models\User;
use AWS\CRT\HTTP\Request;
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
        return false;
    }

    public function delete(User $user, HikingRoute $route)
    {
        return true;
    }



}
