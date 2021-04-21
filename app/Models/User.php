<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property bool is_administrator
 * @property bool is_national_referent
 *
 */
class User extends Authenticatable {
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function region() {
        return $this->belongsTo(Region::class);
    }

    public function provinces() {
        return $this->belongsToMany(Province::class);
    }

    public function areas() {
        return $this->belongsToMany(Area::class);
    }

    public function sectors() {
        return $this->belongsToMany(Sector::class);
    }

    public function getSectors() {
        $sectorsIds = [];

        if ($this->region)
            $sectorsIds = $this->region->sectorsIds();

        if ($this->provinces) {
            foreach ($this->provinces as $province) {
                $sectorsIds = array_merge($sectorsIds, $province->sectorsIds());
            }
        }
        if ($this->areas) {
            foreach ($this->areas as $area) {
                $sectorsIds = array_merge($sectorsIds, $area->sectorsIds());
            }
        }
        if ($this->sectors) {
            foreach ($this->sectors as $sector) {
                $sectorsIds[] = $sector->id;
            }
        }

        $sectorsIds = array_values(array_unique($sectorsIds));
        $result = Sector::whereIn('id', $sectorsIds)->orderBy('full_code', 'ASC')->get();

        return $result;
    }

    /**
     * Get the current logged User
     *
     * @return User
     */
    public static function getLoggedUser(): ?User {
        return isset(auth()->user()->id)
            ? User::find(auth()->user()->id)
            : null;
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
