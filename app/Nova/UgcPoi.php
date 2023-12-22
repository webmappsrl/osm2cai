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
            Text::make('Tassonomie Where', 'taxonomy_wheres'),
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
                $jsonData['position'] = $jsonRawData['position'];
                $jsonData['displayPosition'] = $jsonRawData['displayPosition'];
                $jsonData['city'] = $jsonRawData['city'];
                $jsonData['date'] = $jsonRawData['date'];
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
        return [];
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
