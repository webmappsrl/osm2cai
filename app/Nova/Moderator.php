<?php

namespace App\Nova;

use App\Nova\Actions\EmulateUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Moderator extends Resource {
    public static string $model = \App\Models\User::class;
    public static string $title = 'name';
    public static array $search = [
        'name', 'email',
    ];
    public static string $group = '';

    public static function label() {
        return 'Responsabili';
    }

    private static array $indexDefaultOrder = [
        'name' => 'asc'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array {
        return [
            Text::make('Name')
                ->sortable(),
            Text::make('Email')
                ->sortable()
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),
            Boolean::make(__('Admin'), 'is_administrator')->sortable(),
            Boolean::make(__('National referent'), 'is_national_referent')->sortable(),
            BelongsTo::make('Region')->nullable()
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
     * @return array
     */
    public function cards(Request $request): array {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function filters(Request $request): array {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function lenses(Request $request): array {
        return [];
    }
}
