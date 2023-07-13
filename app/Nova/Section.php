<?php

namespace App\Nova;


use App\Nova\HikingRoute;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\SectionRegionFilter;


class Section extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Section::class;
    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static string $title = 'name';
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'name',
    ];
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static string $group = 'Territorio';


    public static $priority = 5;

    public static function label()
    {
        return 'Sezioni';
    }

    private static $indexDefaultOrder = [
        'name' => 'asc'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function fields(Request $request): array
    {
        $hikingRoutes = $this->hikingRoutes()->get();

        //create a string with the list of all the hiking routes and make it linkable to the hiking route resource detail
        $hikingRoutesString = '';
        foreach ($hikingRoutes as $hikingRoute) {

            $hikingRoutesString .=  "<a style='color:green; text-decoration:none;' href='/resources/hiking-routes/{$hikingRoute->id}'>{$hikingRoute->ref}</a>" . '<br>';
        }
        $hikingRoutesString = rtrim($hikingRoutesString, ', ');
        return [
            ID::make()->sortable()
                ->hideFromIndex(),
            Text::make('Nome', 'name')
                ->sortable()
                ->rules('required', 'max:255'),
            Text::make('Codice CAI', 'cai_code')
                ->sortable()
                ->rules('required', 'max:255'),
            BelongsTo::make('Regione', 'region', Region::class)
                ->searchable(),

            Text::make('Sentieri', function () use ($hikingRoutesString) {
                return $hikingRoutesString;
            })->asHtml()


        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param Request $request
     *
     * @return array
     */
    public function cards(Request $request)
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
    public function filters(Request $request)
    {
        return [
            (new SectionRegionFilter)
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function lenses(Request $request)
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
        return [];
    }
}
