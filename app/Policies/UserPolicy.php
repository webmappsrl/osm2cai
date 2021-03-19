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

    private function _isManager(User $user, User $model)
    {
        $result = false;

        if ($user->is_administrator || $user->id === $model->id)
            $result = true;

        if (!$result && $user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            $result = true;

        if (!$result && $user->region && !$model->is_administrator && !$model->is_national_referent && !$model->region) {
            $provincesIds = $user->region->provincesIds();
            $areasIds = $user->region->areasIds();
            $sectorsIds = $user->region->sectorsIds();

            foreach ($model->provinces->pluck('id') as $id) {
                if (in_array($id, $provincesIds)) {
                    $result = true;
                    break;
                }
            }

            if (!$result) {
                foreach ($model->areas->pluck('id') as $id) {
                    if (in_array($id, $areasIds)) {
                        $result = true;
                        break;
                    }
                }
            }

            if (!$result) {
                foreach ($model->sectors->pluck('id') as $id) {
                    if (in_array($id, $sectorsIds)) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return $this->_isManager($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->is_administrator || $user->is_national_referent;
    }

    public function update(User $user, User $model): bool
    {
        return $this->_isManager($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        $hasRelations = count($model->provinces) + count($model->areas) + count($model->sectors) > 0;
        return !$hasRelations && (
                $user->is_administrator ||
                ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            );
    }

    public function restore(User $user, User $model): bool
    {
        return $user->is_administrator || ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent);
    }

    public function forceDelete(User $user, User $model): bool
    {
        $hasRelations = count($model->provinces) + count($model->areas) + count($model->sectors) > 0;
        return !$hasRelations && (
                $user->is_administrator ||
                ($user->is_national_referent && !$model->is_administrator && !$model->is_national_referent)
            );
    }
}
