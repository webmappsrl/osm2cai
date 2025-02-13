<?php

namespace App\Nova;

use App\Models\Region;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use App\Models\User as UserModel;
use App\Nova\Actions\EmulateUser;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Filters\UserAreaFilter;
use App\Nova\Filters\UserTypeFilter;
use App\Nova\Actions\DownloadUsersCsv;
use App\Nova\Filters\UserRegionFilter;
use App\Nova\Filters\UserSectorFilter;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\UserProvinceFilter;
use App\Nova\Actions\AssociaUtenteAction;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    public static string $model = \App\Models\User::class;
    public static string $title = 'name';
    public static array $search = [
        'name',
        'email',
    ];
    public static string $group = '';

    public static function label()
    {
        return __('Utenti');
    }

    private static array $indexDefaultOrder = [
        'name' => 'asc'
    ];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];
            $query->orderBy(key(static::$indexDefaultOrder), reset(static::$indexDefaultOrder));
        }

        /**
         * @var \App\Models\User
         */
        $user = auth()->user();

        //if user is administrator or national referent
        if ($user->is_administrator || $user->is_national_referent) {
            return $query;
        }

        //if user is regional referent
        if ($user->getTerritorialRole() == 'regional') {
            $regionId = $user->region_id;

            // Get users from territorial hierarchy
            $provinces = Region::find($regionId)->provinces()->get();
            $regionUsers = UserModel::where('region_id', $regionId)->get()->pluck('id')->toArray();
            $provinceUsers = [];
            $areaUsers = [];
            $sectorUsers = [];

            foreach ($provinces as $province) {
                $provinceUsers = array_merge($provinceUsers, $province->users()->get()->pluck('id')->toArray());
                $areas = $province->areas()->get();
                foreach ($areas as $area) {
                    $areaUsers = array_merge($areaUsers, $area->users()->get()->pluck('id')->toArray());
                    $sectors = $area->sectors()->get();
                    foreach ($sectors as $sector) {
                        $sectorUsers = array_merge($sectorUsers, $sector->users()->get()->pluck('id')->toArray());
                    }
                }
            }

            // Get users from sections in the region
            $sectionUsers = UserModel::whereHas('section', function ($query) use ($regionId) {
                $query->whereHas('region', function ($query) use ($regionId) {
                    $query->where('id', $regionId);
                });
            })->pluck('id')->toArray();

            // Get users who manage sections in the region
            $sectionManagerUsers = UserModel::whereHas('managedSection', function ($query) use ($regionId) {
                $query->whereHas('region', function ($query) use ($regionId) {
                    $query->where('id', $regionId);
                });
            })->pluck('id')->toArray();

            // Merge all user IDs and remove duplicates
            $allUsers = array_unique(array_merge(
                $regionUsers,
                $provinceUsers,
                $areaUsers,
                $sectorUsers,
                $sectionUsers,
                $sectionManagerUsers
            ));

            $query->whereIn('id', $allUsers);
        }
        //if user is section manager
        elseif ($user->managedSection) {
            $sectionId = $user->managedSection->id;

            // Get users who are members of the managed section
            $sectionMemberIds = UserModel::whereHas('section', function ($query) use ($sectionId) {
                $query->where('id', $sectionId);
            })->pluck('id')->toArray();

            // Include also the section manager in the list
            $allUsers = array_unique(array_merge([$user->id], $sectionMemberIds));

            $query->whereIn('id', $allUsers);
        }

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        $fields = [
            //            ID::make()->sortable(),
            //            Gravatar::make()->maxWidth(50),
            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255'),
            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),
            Text::make(__('Phone'), 'phone')->sortable()
                ->nullable(),
            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),
            Boolean::make(__('Admin'), 'is_administrator')->sortable()->hideWhenCreating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator;
            })->hideWhenUpdating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator;
            }),
            Boolean::make(__('National referent'), 'is_national_referent')->sortable()->hideWhenCreating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            })->hideWhenUpdating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            }),
            Boolean::make(__('Itinerary Manager'), 'is_itinerary_manager')->sortable()->hideWhenCreating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            })->hideWhenUpdating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            }),
            Boolean::make(__('UGC Tracks Validator'), 'ugc_track_validator')->sortable()->hideWhenCreating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            })->hideWhenUpdating(function () {
                $user = \App\Models\User::getEmulatedUser();

                return !$user->is_administrator && !$user->is_national_referent;
            }),
            BelongsTo::make('Region')->nullable()->help('La regione di cui é referente l\'utente'),
            Date::make('Regional Referent Expire Date', 'regional_referent_expire_date')
                ->nullable()
                ->canSee(function () {
                    return $this->model()->getTerritorialRole() === 'regional';
                })
                ->required(function () {
                    return $this->model()->getTerritorialRole() === 'regional';
                })
                ->readonly(function () {
                    return !auth()->user()->is_administrator;
                })
                ->hideFromIndex()
                ->format('DD/MM/YYYY'),
            Text::make(__('Provinces'), function () {
                $result = [];
                foreach ($this->provinces as $province) {
                    $result[] = $province->name;
                }

                return count($result) > 0 ? implode(', ', $result) : '—';
            }),
            Text::make(__('Areas'), function () {
                $result = [];
                foreach ($this->areas as $area) {
                    $result[] = $area->name;
                }

                return count($result) > 0 ? implode(', ', $result) : '—';
            })->onlyOnDetail(),
            Text::make(__('Sectors'), function () {
                $result = [];
                foreach ($this->sectors as $sector) {
                    $result[] = $sector->name;
                }

                return count($result) > 0 ? implode(', ', $result) : '—';
            })->onlyOnDetail(),
            BelongsToMany::make('Provinces', 'provinces'),
            BelongsToMany::make('Areas', 'areas'),
            BelongsToMany::make('Sectors', 'sectors'),
            Belongsto::make('Section Member', 'section', Section::class)
                ->hideFromIndex()
                ->searchable()
                ->nullable()
                ->help(__('La sezione di cui é membro l\'utente')),
            BelongsTo::make('Managed Section', 'managedSection', Section::class)
                ->nullable()
                ->searchable()
                ->help(__('La sezione di cui l\'utente é responsabile')),
            Date::make('Section Manager Expire Date', 'section_manager_expire_date')
                ->nullable()
                ->canSee(function ($request) {
                    return $this->managedSection;
                })
                ->required(function () {
                    return $this->model()->getPermissionString() === 'Responsabile sezione';
                })
                ->readonly(function () {
                    return !auth()->user()->is_administrator;
                })
                ->hideFromIndex()
                ->format('DD/MM/YYYY'),
            Code::make('Default overpass query', 'default_overpass_query')
                ->onlyOnDetail(),
            Code::make('Default overpass query', 'default_overpass_query')
                ->onlyOnForms()
                ->canSee(function ($request) {
                    return auth()->user()->is_administrator || auth()->user()->is_national_referent || auth()->user()->id == $request->resourceId;
                })
                ->help('Utilizzare placeholder " @osm_id " per sostituire con l\'id della relazione(e.g. rel(@osm_id);node(around:1000)["amenity"~"monastery|place_of_worship|ruins"];);out;')
        ];

        $validationFields = $this->jsonForm('resources_validator', $this->getValidatorFieldsSchema());

        array_push($fields, $validationFields);

        return $fields;
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
     * @return array
     */
    public function cards(Request $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function filters(Request $request): array
    {
        return [
            (new UserTypeFilter)->canSee(function ($request) {
                return auth()->user()->is_administrator || auth()->user()->is_national_referent;
            }),
            (new UserRegionFilter),
            (new UserAreaFilter),
            (new UserProvinceFilter),
            (new UserSectorFilter)
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function lenses(Request $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function actions(Request $request): array
    {
        return [
            (new EmulateUser())
                ->canSee(function ($request) {
                    return $request->user()->can('emulate', $this->resource);
                })
                ->canRun(function ($request, $zone) {
                    return $request->user()->can('emulate', $zone);
                }),
            (new DownloadUsersCsv())
                ->canSee(function ($request) {
                    return auth()->user()->is_administrator || auth()->user()->is_national_referent;
                })
                ->canRun(function ($request, $zone) {
                    return auth()->user()->is_administrator || auth()->user()->is_national_referent;
                }),
        ];
    }

    public static function relatableProvinces(NovaRequest $request, $query)
    {
        $emulateUserId = session('emulate_user_id');
        $user = $request->user();

        if (isset($emulateUserId))
            $user = \App\Models\User::find($emulateUserId);

        if (!$user->is_administrator && !$user->is_national_referent) {
            $ids = [];

            if ($user->region)
                $ids = $user->region->provincesIds();

            $query = $query->whereIn('id', $ids);
        }

        return $query;
    }

    public static function relatableAreas(NovaRequest $request, $query)
    {
        $emulateUserId = session('emulate_user_id');
        $user = $request->user();

        if (isset($emulateUserId))
            $user = \App\Models\User::find($emulateUserId);

        if (!$user->is_administrator && !$user->is_national_referent) {
            $ids = [];

            if ($user->region_id)
                $ids = $user->region->areasIds();

            $query = $query->whereIn('id', $ids);
        }

        return $query;
    }

    public static function relatableSectors(NovaRequest $request, $query)
    {
        $emulateUserId = session('emulate_user_id');
        $user = $request->user();

        if (isset($emulateUserId))
            $user = \App\Models\User::find($emulateUserId);

        if (!$user->is_administrator && !$user->is_national_referent) {
            $ids = [];

            if ($user->region)
                $ids = $user->region->sectorsIds();

            $query = $query->whereIn('id', $ids);
        }

        return $query;
    }

    public static function actionOnIndex()
    {
        return null;
    }
}
