<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AreaPolicy
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

    public function view(User $user, Area $area)
    {
        return false;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Area $area)
    {
        return false;
    }

    public function delete(User $user, Area $area)
    {
        return false;
    }

    public function restore(User $user, Area $area)
    {
        return false;
    }

    public function forceDelete(User $user, Area $area)
    {
        return false;
    }

    public function downloadGeojson(User $user, Area $area)
    {
        return true;
    }

    public function downloadShape(User $user, Area $area)
    {
        return true;
    }
}
