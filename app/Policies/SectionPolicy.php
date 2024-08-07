<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Section;
use App\Models\HikingRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionPolicy
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
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Section $section)
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
        return $user->is_administrator;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Section $section)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Section $section)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Section $section)
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Section $section)
    {
        return true;
    }

    /**
     * Determine whether the user can attach any hikingRoute to the Section.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @return mixed
     */
    public function attachAnyHikingRoute(User $user, Section $section)
    {
        return false;
    }

    /**
     * Determine whether the user can attach a hr to a section.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Section  $section
     * @param  \App\Models\HikingRoute  $hikingRoute
     * @return mixed
     */
    public function attachHikingRoute(User $user, Section $podcast, HikingRoute $hikingRoute)
    {
        return false;
    }
}
