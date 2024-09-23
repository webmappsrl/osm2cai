<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use App\Enums\UgcValidatedStatus;
use Laravel\Nova\Fields\DateTime;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\BelongsTo;
use Wm\MapPointNova3\MapPointNova3;
use App\Nova\Filters\UgcAppIdFilter;
use App\Nova\Filters\UgcFormIdFilter;
use App\Nova\Filters\ValidatedFilter;
use App\Nova\Filters\RelatedUGCFilter;
use Laravel\Nova\Fields\BelongsToMany;
use App\Nova\Filters\UgcUserNoMatchFilter;
use PosLifestyle\DateRangeFilter\Enums\Config;
use PosLifestyle\DateRangeFilter\DateRangeFilter;

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

        $formIdOptions = [
            'paths' => 'Sentieristica',
            'report' =>  'Segnalazione Problemi',
            'poi' => 'Punti di Interesse',
            'water' => 'Acqua Sorgente',
            'signs' => 'Segni dell\'uomo',
            'archaeological_area' => 'Aree Archeologiche',
            'archaeological_site' => 'Siti Archeologici',
            'geological_site' => 'Siti Geologici',
        ];

        if ($request->isCreateOrAttachRequest()) {
            return [
                Select::make('Form ID', 'form_id')
                    ->options($formIdOptions)
                    ->rules('required')
                    ->help('Seleziona il tipo di UGC che vuoi creare. Dopo il salvataggio, potrai inserire tutti i dettagli.'),
            ];
        }

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
            BelongsTo::make('User', 'user', User::class)
                ->searchable()
                ->hideWhenUpdating()
                ->hideWhenCreating()
                ->hideFromIndex()
                ->hideFromDetail(),
            Select::make('Validated', 'validated')
                ->options(UgcValidatedStatus::cases())
                ->canSee(function ($request) {
                    return $request->user()->isValidatorForFormId($this->form_id) ?? false;
                })->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $isValidated = $request->$requestAttribute;
                    $model->$attribute = $isValidated;
                    //logica per tracciare validatore e data di validazione

                    if ($isValidated == UgcValidatedStatus::Valid) {
                        $model->validator_id = $request->user()->id;
                        $model->validation_date = now();
                    } else {
                        $model->validator_id = null;
                        $model->validation_date = null;
                    }
                })->onlyOnForms(),
            Text::make('Validation Status', function () {
                return $this->validated;
            }),
            DateTime::make('Validation Date', 'validation_date')
                ->format('DD MMM YYYY HH:mm:ss')
                ->onlyOnDetail(),
            Text::make('Validator', function () {
                if ($this->validator_id) {
                    return $this->validator->name;
                } else {
                    return null;
                }
            })->onlyOnDetail(),
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
            DateTime::make('Registered At', 'registered_at')
                ->format('DD MMM YYYY HH:mm:ss')
                ->readonly(),
            DateTime::make('Updated At')
                ->format('DD MMM YYYY HH:mm:ss')
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->sortable(),
            Text::make('Geohub ID', 'geohub_id')
                ->onlyOnDetail(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 5,
                'maxZoom' => 14,
                'defaultZoom' => 5
            ])->hideFromIndex(),
            Code::make(__('Form data'), function ($model) {
                $jsonRawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data ?? null;
                if ($jsonRawData) {
                    unset($jsonRawData['position']);
                    unset($jsonRawData['displayPosition']);
                    unset($jsonRawData['city']);
                    unset($jsonRawData['date']);
                    unset($jsonRawData['nominatim']);
                    $jsonRawData = json_encode($jsonRawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
                return $jsonRawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Device data'), function ($model) {
                $jsonRawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data ?? null;
                if ($jsonRawData) {
                    $jsonData['position'] = $jsonRawData['position'] ?? null;
                    $jsonData['displayPosition'] = $jsonRawData['displayPosition'] ?? null;
                    $jsonData['city'] = $jsonRawData['city'] ?? null;
                    $jsonData['date'] = $jsonRawData['date'] ?? null;
                    $jsonRawData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
                return $jsonRawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Nominatim'), function ($model) {
                $jsonData = is_string($model->raw_data) ? json_decode($model->raw_data, true)['nominatim'] : $model->raw_data['nominatim'] ?? null;
                if ($jsonData) {
                    $jsonData = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
                return $jsonData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Code::make(__('Raw data'), function ($model) {
                $rawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data ?? null;
                if ($rawData) {
                    $rawData = json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
                return $rawData;
            })->onlyOnDetail()->language('json')->rules('json'),
            Text::make('Gallery', function () {
                $images = $this->ugc_media;
                $html = '<div style="display: flex; flex-wrap: wrap;">';
                foreach ($images as $image) {
                    $url = $image->getUrl();
                    $html .= '<div style="margin: 5px; text-align: center;">';
                    $html .= '<a href="' . $url . '" target="_blank">';
                    $html .= '<img src="' . $url . '" width="100" height="100" style="object-fit: cover;">';
                    $html .= '</a>';
                    $html .= '<p style="color: lightgray;">ID: ' . $image->id . '</p>';
                    $html .= '</div>';
                }
                $html .= '</div>';
                return $html;
            })->asHtml()->onlyOnDetail(),
        ];

        if ($this->form_id == 'poi') {
            array_splice($commonFields, array_search('user', array_column($commonFields, 'name')), 0, [Text::make('Poi Type', 'raw_data->waypointtype')->onlyOnDetail()]);
        }

        $formFields = $this->jsonForm('raw_data');

        if (!empty($formFields)) {
            array_push(
                $commonFields,
                $formFields,
            );
        }

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
            (new ValidatedFilter()),
            (new UgcFormIdFilter()),
            (new UgcAppIdFilter()),
            (new DateRangeFilter(
                'Registered At',
                'raw_data->date',
                [
                    Config::DATE_FORMAT => 'd-m-Y',
                    Config::SHORTHAND_CURRENT_MONTH => true,
                    Config::ENABLE_TIME => true,
                    Config::ENABLE_SECONDS => true,
                ]
            )),
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
            })->standalone(),
            (new \App\Nova\Actions\UploadAndAssociateUgcMedia())->canSee(function ($request) {
                if ($this->user_id)
                    return auth()->user()->id == $this->user_id;
                if ($request->has('resources'))
                    return true;

                return false;
            })
                ->canRun(function ($request) {
                    return true;
                })
                ->confirmText('Sei sicuro di voler caricare questa immagine?')
                ->confirmButtonText('Carica')
                ->cancelButtonText('Annulla'),
            (new \App\Nova\Actions\DeleteUgcMedia($this->model()))->canSee(function ($request) {
                if ($this->user_id)
                    return auth()->user()->id == $this->user_id;
                if ($request->has('resources'))
                    return true;

                return false;
            })
        ];
    }

    public static function authorizedToCreate(Request $request)
    {
        return true;
    }

    public static function redirectAfterCreate(Request $request, $resource)
    {
        return '/resources/ugc-pois/' . $resource->id . '/edit';
    }
}
