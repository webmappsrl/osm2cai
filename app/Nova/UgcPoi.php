<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use App\Enums\UgcValidatedStatus;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapPointNova3\MapPointNova3;
use Illuminate\Support\Facades\Auth;
use App\Nova\Filters\UgcFormIdFilter;
use App\Nova\Filters\RelatedUGCFilter;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\UgcUserNoMatchFilter;
use DKulyk\Nova\Tabs;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

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
        'id',
        'name',
        'user_no_match'
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
        $commonFields = [
            ID::make(__('ID'), 'id')
                ->sortable()
                ->readonly()
                ->showOnCreating()
                ->showOnUpdating(),
            Text::make('User', function () {
                if ($this->user_id) {
                    return '<a style="text-decoration:none; font-weight:bold; color:teal;" href="/resources/users/' . $this->user_id . '">' . $this->user->name . '</a>';
                } else {
                    return $this->user_no_match;
                }
            })->asHtml(),
            Select::make('Validated', 'validated')
                ->options(UgcValidatedStatus::cases()),
            Text::make('App ID', 'app_id')
                ->onlyOnDetail(),
            Text::make('Form ID', 'form_id')->resolveUsing(function ($value) {
                if ($this->raw_data and isset($this->raw_data['id'])) {
                    return $this->raw_data['id'];
                } else {
                    return $value;
                }
            })
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            DateTime::make('Updated At')
                ->format('DD MMM YYYY HH:mm:ss')
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            Text::make('Geohub ID', 'geohub_id')
                ->onlyOnDetail(),
            // Text::make('Nome', 'name')
            //     ->sortable()
            //     ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
            //         $model->{$attribute} = $request->{$requestAttribute};
            //         $rawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
            //         $rawData['title'] = $request->{$requestAttribute};
            //         $model->raw_data = $rawData;
            //         $model->save();
            //     }),
            // Textarea::make('Descrizione', 'description')
            //     ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
            //         $model->{$attribute} = $request->{$requestAttribute};
            //         $rawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
            //         $rawData['description'] = $request->{$requestAttribute};
            //         $model->raw_data = $rawData;
            //         $model->save();
            //     }),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),
            Code::make(__('Form data'), function ($model) {
                $jsonRawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
                unset($jsonRawData['position']);
                unset($jsonRawData['displayPosition']);
                unset($jsonRawData['city']);
                unset($jsonRawData['date']);
                unset($jsonRawData['nominatim']);
                $rawData = json_encode($jsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Device data'), function ($model) {
                $jsonRawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
                $jsonData['position'] = $jsonRawData['position'] ?? null;
                $jsonData['displayPosition'] = $jsonRawData['displayPosition'] ?? null;
                $jsonData['city'] = $jsonRawData['city'] ?? null;
                $jsonData['date'] = $jsonRawData['date'] ?? null;
                $rawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Nominatim'), function ($model) {
                $jsonData = is_string($model->raw_data) ? json_decode($model->raw_data, true)['nominatim'] : $model->raw_data['nominatim'];
                $jsonData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                return $jsonData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Raw data'), function ($model) {
                $rawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
                return json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            })->onlyOnDetail()->language('json')->rules('json'),
        ];

        $formFields = $this->jsonForm('raw_data');

        if (!empty($formFields)) {
            array_push(
                $commonFields,
                $formFields,
            );
        }
        array_push($commonFields, BelongsToMany::make('Gallery', 'ugc_media', UgcMedia::class));

        if (empty(static::$activeFields)) {
            return $commonFields;
        }

        return array_filter($commonFields, function ($field) {
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
            (new RelatedUGCFilter()),
            (new UgcFormIdFilter()),
            (new UgcUserNoMatchFilter()),
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
