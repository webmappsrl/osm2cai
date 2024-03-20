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
        'id', 'name',
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
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')
                ->sortable(),
            DateTime::make('Updated At')
                ->format('DD MMM YYYY HH:mm:ss')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make('Geohub ID', 'geohub_id')
                ->onlyOnDetail(),
            Text::make('Nome', 'name')
                ->sortable(),
            Textarea::make('Descrizione', 'description'),
            BelongsTo::make('User', 'user')
                ->searchable()
                ->sortable(),
            BelongsToMany::make('Media', 'ugc_media', UgcMedia::class),
            Text::make('Taxonomy wheres', function () {
                //split the string by ','
                $array = explode(',', $this->taxonomy_wheres);
                //get only the first value of the array
                $result = $array[0];
                //add ... if the array has more than one element
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
                return  $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Device data'), function ($model) {
                $jsonRawData = json_decode($model->raw_data, true);
                $jsonData['position'] = $jsonRawData['position'] ?? null;
                $jsonData['displayPosition'] = $jsonRawData['displayPosition'] ?? null;
                $jsonData['city'] = $jsonRawData['city'] ?? null;
                $jsonData['date'] = $jsonRawData['date'] ?? null;
                $rawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return  $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Nominatim'), function ($model) {
                $jsonData = json_decode($model->raw_data, true)['nominatim'];
                $rawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return  $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Raw data'), function ($model) {
                $rawData = json_encode(json_decode($model->raw_data, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return  $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Text::make('Form ID', 'form_id')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make('User no Match', 'user_no_match')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}