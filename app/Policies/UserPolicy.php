<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\Province;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy {
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Return true if the given user is a manager of the model user
     *
     * @param User $user
     * @param User $model
     * @param bool $self if the user is manager of himself
     *
     * @return bool
     */
    private function _isManager(User $user, User $model, bool $self = true): bool {
        if ($user->id === $model->id)
            return $self;

        if ($user->is_administrator)
            return true;

        if ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            return true;

        return false;
    }

    public function viewAny(User $user): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function view(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function create(User $user): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function update(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function delete(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function restore(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function forceDelete(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function emulate(User $user, User $model): bool {
        return $user->is_administrator || $user->is_national_referent;
    }

    private function _canProvince(User $user, Province $province) {
        $user = User::getEmulatedUser($user);
        $result = false;

        if ($user->is_administrator || $user->is_national_referent)
            $result = true;
        else if (isset($user->region_id)) {
            $ids = $user->region->provincesIds();

            if (in_array($province->id, $ids))
                $result = true;
        }

        return $result;
    }

    private function _canArea(User $user, Area $area) {
        $user = User::getEmulatedUser($user);
        $result = false;

        if ($user->is_administrator || $user->is_national_referent)
            $result = true;
        else if (isset($user->region_id)) {
            $ids = $user->region->areasIds();

            if (in_array($area->id, $ids))
                $result = true;
        }

        return $result;
    }

    private function _canSector(User $user, Sector $sector) {
        $user = User::getEmulatedUser($user);
        $result = false;

        if ($user->is_administrator || $user->is_national_referent)
            $result = true;
        else if (isset($user->region_id)) {
            $ids = $user->region->sectorsIds();

            if (in_array($sector->id, $ids))
                $result = true;
        }

        return $result;
    }

    public function attachProvince(User $user, User $model, Province $province) {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function detachProvince(User $user, User $model, Province $province) {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function attachArea(User $user, User $model, Area $area) {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function detachArea(User $user, User $model, Area $area) {
        return $user->is_administrator;
    }

    public function attachSector(User $user, User $model, Sector $sector) {
        return $this->_canSector($user, $sector);
    }

    public function detachSector(User $user, User $model, Sector $sector) {
        return $this->_canSector($user, $sector);
    }
}
