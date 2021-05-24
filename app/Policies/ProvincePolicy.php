<?php

namespace App\Policies;

use App\Models\Province;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProvincePolicy
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

    public function view(User $user, Province $province)
    {
        return false;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Province $province)
    {
        return false;
    }

    public function delete(User $user, Province $province)
    {
        return false;
    }

    public function restore(User $user, Province $province)
    {
        return false;
    }

    public function forceDelete(User $user, Province $province)
    {
        return false;
    }

    public function downloadGeojson(User $user, Province $province)
    {
        return true;
    }

    public function downloadShape(User $user, Province $province)
    {
        return true;
    }

    public function downloadKml(User $user, Province $province)
    {
        return true;
    }
}
