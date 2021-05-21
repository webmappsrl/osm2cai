<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegionPolicy
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

    public function view(User $user, Region $region)
    {
        return false;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Region $region)
    {
        return false;
    }

    public function delete(User $user, Region $region)
    {
        return false;
    }

    public function restore(User $user, Region $region)
    {
        return false;
    }

    public function forceDelete(User $user, Region $region)
    {
        return false;
    }

    public function downloadGeojson(User $user, Region $region)
    {
        return true;
    }

    public function downloadShape(User $user, Region $region)
    {
        return true;
    }

    public function downloadKml(User $user, Region $region)
    {
        return true;
    }
}
