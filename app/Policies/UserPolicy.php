<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Return true if the given user is a manager of the model user
     *
     * @param User $user
     * @param User $model
     * @param bool $self if the user is manager of himself
     * @return bool
     */
    private function _isManager(User $user, User $model, bool $self = true): bool
    {
        if ($user->id === $model->id)
            return $self;

        if ($user->is_administrator)
            return true;

        if ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            return true;

        if ($user->region && !$model->is_administrator && !$model->is_national_referent && !$model->region) {
            $provincesIds = $user->region->provincesIds();
            $areasIds = $user->region->areasIds();
            $sectorsIds = $user->region->sectorsIds();

            foreach ($model->provinces->pluck('id') as $id) {
                if (in_array($id, $provincesIds))
                    return true;
            }

            foreach ($model->areas->pluck('id') as $id) {
                if (in_array($id, $areasIds))
                    return true;
            }

            foreach ($model->sectors->pluck('id') as $id) {
                if (in_array($id, $sectorsIds))
                    return true;
            }
        }

        return false;
    }

    private function _getEmulatedUser(User $user): User
    {
        $result = $user;
        $emulateUserId = session('emulate_user_id');
        if (isset($emulateUserId))
            $result = User::find($emulateUserId);
        return $result;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        return $this->_isManager($user, $model);
    }

    public function create(User $user): bool
    {
        $user = $this->_getEmulatedUser($user);
        return $user->is_administrator || $user->is_national_referent;
    }

    public function update(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        return $this->_isManager($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        $hasRelations = count($model->provinces) + count($model->areas) + count($model->sectors) > 0;
        return !$hasRelations && (
                $user->is_administrator ||
                ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            );
    }

    public function restore(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        return $user->is_administrator || ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent);
    }

    public function forceDelete(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        $hasRelations = count($model->provinces) + count($model->areas) + count($model->sectors) > 0;
        return !$hasRelations && (
                $user->is_administrator ||
                ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            );
    }

    public function emulate(User $user, User $model): bool
    {
        $user = $this->_getEmulatedUser($user);
        return $this->_isManager($user, $model, false);
    }
}
