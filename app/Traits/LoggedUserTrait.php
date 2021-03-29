<?php

namespace App\Traits;

use App\Models\User;

class LoggedUserTrait {
    /**
     * Get the current logged User
     *
     * @return User
     */
    public static function getLoggedUser(): ?User {
        return User::find(auth()->user()->id) ?? null;
    }

    /**
     * Get the current emulated User
     *
     * @param User|null $user
     *
     * @return User
     */
    public static function getEmulatedUser(User $user = null): User {
        if (!isset($user)) $user = self::getLoggedUser();

        $result = $user;
        $emulateUserId = session('emulate_user_id');
        if (isset($emulateUserId))
            $result = User::find($emulateUserId);

        return $result;
    }
}
