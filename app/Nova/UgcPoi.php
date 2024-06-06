<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapPointNova3\MapPointNova3;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Http\Requests\NovaRequest;

class UgcPoi extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\UgcPoi::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public function title()
    {
        if ($this->name)
            return "{$this->name} ({$this->id})";
        else
            return "{$this->id}";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static array $search = [
        'id', 'name', 'user_no_match'
    ];

    /**
     * The relationship columns that should be searched
     * @var array
     */
    public static $searchRelations = [
        'user' => ['name', 'email'],
    ];

    public static string $group = 'Rilievi';
    public static $priority = 1;

    public static function label()
    {
        $label = 'Poi';

        return __($label);
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        if (Auth::user()->getTerritorialRole() === 'regional' || Auth::user()->getTerritorialRole() === 'local') {
            return $query->where('user_id', Auth::user()->id);
        }
    }

    /**
     * Array of fields to activate.
     *
     * @var array
     */
    protected static $activeFields = [];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $allFields = [
            ID::make(__('ID'), 'id')
                ->sortable()
                ->readonly()
                ->showOnCreating()
                ->showOnUpdating(),
            Text::make('Form ID', function () {
                $rawData = json_decode($this->raw_data, true);
                return $this->form_id ?? $rawData['id'] ?? null;
            })
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            DateTime::make('Updated At')
                ->format('DD MMM YYYY HH:mm:ss')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make('Geohub ID', 'geohub_id')
                ->onlyOnDetail(),
            Text::make('Nome', 'name')
                ->sortable(),
            Textarea::make('Descrizione', 'description'),
            Text::make('User', function () {
                if ($this->user_id) {
                    return '<a style="text-decoration:none; font-weight:bold; color:teal;" href="/resources/users/' . $this->user_id . '">' . $this->user->name . '</a>';
                } else {
                    return $this->user_no_match;
                }
            })->asHtml(),
            Text::make('User No Match', 'user_no_match')
                ->onlyOnDetail(),
            BelongsToMany::make('Media', 'ugc_media', UgcMedia::class),
            Text::make('Taxonomy wheres', function () {
                $array = explode(',', $this->taxonomy_wheres);
                $result = $array[0];
                if (count($array) > 1) {
                    $result .= '[...]';
                }
                return $result;
            })->onlyOnIndex(),
            Text::make('Taxonomy wheres', 'taxonomy_wheres')
                ->hideFromIndex(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),
            Code::make(__('Form data'), function ($model) {
                $jsonRawData = json_decode($model->raw_data, true);
                unset($jsonRawData['position']);
                unset($jsonRawData['displayPosition']);
                unset($jsonRawData['city']);
                unset($jsonRawData['date']);
                unset($jsonRawData['nominatim']);
                $rawData = json_encode($jsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Device data'), function ($model) {
                $jsonRawData = json_decode($model->raw_data, true);
                $jsonData['position'] = $jsonRawData['position'] ?? null;
                $jsonData['displayPosition'] = $jsonRawData['displayPosition'] ?? null;
                $jsonData['city'] = $jsonRawData['city'] ?? null;
                $jsonData['date'] = $jsonRawData['date'] ?? null;
                $rawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Nominatim'), function ($model) {
                $jsonData = json_decode($model->raw_data, true)['nominatim'];
                $rawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Raw data'), function ($model) {
                $rawData = json_encode(json_decode($model->raw_data, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
        ];

        if (empty(static::$activeFields)) {
            return $allFields;
        }

        return array_filter($allFields, function ($field) {
            return in_array($field->name, static::$activeFields);
        });
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
        return [
            (new \App\Nova\Filters\UgcFormIdFilter()),
            (new \App\Nova\Filters\UgcUserNoMatchFilter()),
        ];
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
            (new \App\Nova\Actions\DownloadUgcCsv()),
            (new \App\Nova\Actions\CheckUserNoMatchAction)->canRun(function () {
                return true;
            })->standalone()
        ];
    }
}
