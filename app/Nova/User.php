<?php

namespace App\Nova;

use App\Nova\Actions\EmulateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class User extends Resource
{
    public static $model = \App\Models\User::class;
    public static $title = 'name';
    public static $search = [
        'name', 'email',
    ];
    public static $group = '';

    public static function label()
    {
        return __('Utenti');
    }

    private static $indexDefaultOrder = [
        'name' => 'asc'
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (empty($request->get('orderBy'))) {
            $query->getQuery()->orders = [];
            return $query->orderBy(key(static::$indexDefaultOrder), reset(static::$indexDefaultOrder));
        }
        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
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
            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8'),
            Boolean::make(__('Admin'), 'is_administrator')->sortable()->hideWhenCreating(function ($request) {
                $user = auth()->user();
                return !$user->is_administrator;
            })->hideWhenUpdating(function ($request) {
                $user = auth()->user();
                return !$user->is_administrator;
            }),
            Boolean::make(__('National referent'), 'is_national_referent')->sortable()->hideWhenCreating(function ($request) {
                $user = auth()->user();
                return !$user->is_administrator && !$user->is_national_referent;
            })->hideWhenUpdating(function ($request) {
                $user = auth()->user();
                return !$user->is_administrator && !$user->is_national_referent;
            }),
            BelongsTo::make('Region'),
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
            BelongsToMany::make('Provinces'),
            BelongsToMany::make('Areas'),
            BelongsToMany::make('Sectors')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new EmulateUser())
                ->canSee(function ($request) {
                    return $request->user()->can('emulate', $this->resource);
                })
                ->canRun(function ($request, $zone) {
                    return $request->user()->can('emulate', $zone);
                }),
        ];
    }
}
