<?php

namespace App\Policies;

use App\Models\EcPoi;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EcPoiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EcPoi  $ecPoi
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, EcPoi $ecPoi)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->getTerritorialRole() !== 'unknown';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EcPoi  $ecPoi
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, EcPoi $ecPoi)
    {
        return $user->getTerritorialRole() !== 'unknown';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EcPoi  $ecPoi
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, EcPoi $ecPoi)
    {
        return $user->getTerritorialRole() !== 'unknown';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EcPoi  $ecPoi
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, EcPoi $ecPoi)
    {
        return $user->getTerritorialRole() !== 'unknown';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EcPoi  $ecPoi
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, EcPoi $ecPoi)
    {
        return $user->getTerritorialRole() !== 'unknown';
    }
}
