<?php

namespace App\Policies;

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
        return false;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Sector $sector)
    {
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
}
