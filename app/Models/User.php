<?php

namespace App\Models;

use App\Models\Section;
use App\Traits\WmNovaFieldsTrait;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property bool is_administrator
 * @property bool is_national_referent
 *
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, WmNovaFieldsTrait;

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
        'resources_validator' => 'array',
        'regional_referent_expire_date' => 'date',
        'section_manager_expire_date' => 'date',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function provinces()
    {
        return $this->belongsToMany(Province::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class);
    }

    public function sectors()
    {
        return $this->belongsToMany(Sector::class);
    }

    public function hikingRoutes()
    {
        return $this->belongsToMany(HikingRoutes::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function managedSection()
    {
        return $this->belongsTo(Section::class, 'manager_section_id');
    }

    public function ecPois()
    {
        return $this->hasMany(EcPoi::class);
    }

    public function ugcPois()
    {
        return $this->hasMany(UgcPoi::class);
    }

    public function ugcTracks()
    {
        return $this->hasMany(UgcTrack::class);
    }

    public function ugcMedia()
    {
        return $this->hasMany(UgcMedia::class);
    }

    public function getSectors()
    {
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
     * @return string
     */
    public function getPermissionString(): string
    {
        if ($this->is_administrator) {
            return 'Superadmin';
        } else if ($this->is_national_referent) {
            return 'Referente nazionale';
        } else if (!is_null($this->region_id)) {
            return 'Referente regionale';
        } else if (
            count($this->provinces) > 0
            || count($this->areas) > 0
            || count($this->sectors) > 0
        ) {
            return 'Referente di zona';
        } else if (!is_null($this->managedSection)) {
            return 'Responsabile sezione';
        }
        return 'Unknown';
    }

    public function getTerritorialRole(): string
    {
        $role = 'unknown';
        if ($this->is_administrator) {
            $role = 'admin';
        } else if ($this->is_national_referent) {
            return 'national';
        } else if (!is_null($this->region_id)) {
            return 'regional';
        } else if (
            count($this->provinces) > 0
            || count($this->areas) > 0
            || count($this->sectors) > 0
        ) {
            return 'local';
        }
        return $role;
    }

    public function isValidatorForFormId(?string $formId)
    {
        if (!$formId) {
            return false;
        }

        $resourcesValidator = $this->resources_validator ?? [];

        switch ($formId) {
            case 'water':
                return $resourcesValidator['is_source_validator'] ?? false;
            case 'archaeological_area':
                return $resourcesValidator['is_archaeological_area_validator'] ?? false;
            case 'archaeological_site':
                return $resourcesValidator['is_archaeological_site_validator'] ?? false;
            case 'signs':
                return $resourcesValidator['is_signs_validator'] ?? false;
            case 'geological_site':
                return $resourcesValidator['is_geological_site_validator'] ?? false;
            default:
                return false;
        }
    }

    public function getValidatorFieldsSchema(): array
    {
        return [
            [
                'name' => 'is_source_validator',
                'label' => [
                    'it' => 'Validatore Sorgenti',
                    'en' => 'Source Validator',
                ],
                'type' => 'boolean',
            ],
            [
                'name' => 'is_archaeological_area_validator',
                'label' => [
                    'it' => 'Validatore Aree Archeologiche',
                    'en' => 'Archaeological Area Validator',
                ],
                'type' => 'boolean',
            ],
            [
                'name' => 'is_signs_validator',
                'label' => [
                    'it' => 'Validatore Segni',
                    'en' => 'Signs Validator',
                ],
                'type' => 'boolean',
            ],
            [
                'name' => 'is_geological_site_validator',
                'label' => [
                    'it' => 'Validatore Siti Geologici',
                    'en' => 'Geological Site Validator',
                ],
                'type' => 'boolean',
            ],
            [
                'name' => 'is_archaeological_site_validator',
                'label' => [
                    'it' => 'Validatore Siti Archeologici',
                    'en' => 'Archaeological Site Validator',
                ],
                'type' => 'boolean',
            ],
        ];
    }

    /**
     * Get the current logged User
     *
     * @return User
     */
    public static function getLoggedUser(): ?User
    {
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
    public static function getEmulatedUser(User $user = null): User
    {
        if (!isset($user)) $user = self::getLoggedUser();

        $result = $user;
        $emulateUserId = session('emulate_user_id');
        if (isset($emulateUserId))
            $result = User::find($emulateUserId);

        return $result;
    }

    public function scopeOfRegion($query, Region $region)
    {
        $regionCode = $region->code;

        $query->whereHas('provinces', function ($query) use ($regionCode) {
            $query->where('full_code', 'LIKE', $regionCode . '%');
        })
            ->orWherehas('areas', function ($query) use ($regionCode) {
                $query->where('full_code', 'LIKE', $regionCode . '%');
            })
            ->orWhereHas('sectors', function ($query) use ($regionCode) {
                $query->where('full_code', 'LIKE', $regionCode . '%');
            });
    }

    public function canManageHikingRoute(HikingRoute $hr)
    {
        $role = $this->getTerritorialRole();
        switch ($role) {
            case 'unknown':
                return false;
                break;
            case 'admin':
                return true;
                break;
            case 'national':
                return true;
                break;
            case 'regional':
                $manage = false;
                foreach ($hr->regions()->get() as $r) {
                    if ($manage)
                        continue;
                    if ($this->region_id == $r->id)
                        $manage = true;
                }
                return $manage;
                break;
            case 'local':
                $manage = false;
                if (count($this->areas) > 0) {
                    foreach ($this->areas as $item) {
                        foreach ($hr->areas()->get() as $hr_item) {
                            if ($item->id == $hr_item->id) {
                                $manage = true;
                            }
                        }
                    }
                }
                if (count($this->sectors) > 0) {
                    foreach ($this->sectors as $item) {
                        foreach ($hr->sectors()->get() as $hr_item) {
                            if ($item->id == $hr_item->id) {
                                $manage = true;
                            }
                        }
                    }
                }
                if (count($this->provinces) > 0) {
                    foreach ($this->provinces as $item) {
                        foreach ($hr->provinces()->get() as $hr_item) {
                            if ($item->id == $hr_item->id) {
                                $manage = true;
                            }
                        }
                    }
                }
                if ($manage)
                    return true;
                else
                    return false;
                break;
        }
    }

    public function canManageSection($section)
    {
        return $this->is_administrator || $this->is_national_referent || ($this->getTerritorialRole() == 'regional' && $this->region_id == $section->region_id) || (!is_null($this->managedSection) && $this->managedSection->id == $section->id);
    }
}
