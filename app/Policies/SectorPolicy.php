<?php

namespace App\Policies;

use App\Models\HikingRoute;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectorPolicy
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

    public function view(User $user, Sector $sector)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->is_administrator;
    }

    public function update(User $user, Sector $sector)
    {
        if($user->is_administrator) {
            return true;
        }
        if($user->is_national_referent) {
            return true;
        }
        if($user->region_id == $sector->area->province->region->id) {
            return true;
        }
        return false;
    }

    public function delete(User $user, Sector $sector)
    {
        return false;
    }

    public function restore(User $user, Sector $sector)
    {
        return false;
    }

    public function forceDelete(User $user, Sector $sector)
    {
        return false;
    }

    public function downloadGeojson(User $user, Sector $sector)
    {
        return true;
    }

    public function downloadShape(User $user, Sector $sector)
    {
        return true;
    }

    public function downloadKml(User $user, Sector $sector)
    {
        return true;
    }

    public function attachHikingRoute(User $user, Sector $sector)
    {
        return false;
    }

    public function detachHikingRoute(User $user, Sector $sector)
    {
        return false;
    }

    public function attachAnyHikingRoute(User $user, Sector $sector)
    {
        return false;
    }

    public function attachUser(User $user, Sector $sector, $userToAttach)
    {
        return ! $sector->users->contains($userToAttach);
    }

    public function bulkAssignUser( User $user, Sector $sector )
    {
        return true;
    }
}
