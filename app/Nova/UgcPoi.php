<?php

namespace App\Nova;

use App\Nova\AbstractUgc;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use App\Enums\UgcValidatedStatus;
use Wm\MapPointNova3\MapPointNova3;
use App\Nova\Actions\DeleteUgcMedia;
use App\Nova\Actions\DownloadUgcCsv;
use App\Nova\Filters\UgcFormIdFilter;
use App\Nova\Actions\CheckUserNoMatchAction;
use App\Nova\Actions\DownloadFeatureCollection;
use App\Nova\Actions\UploadAndAssociateUgcMedia;

class UgcPoi extends AbstractUgc
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
        'user_no_match',
    ];

    /**
     * The relationship columns that should be searched
     * @var array
     */
    public static $searchRelations = [
        'user' => ['name', 'email'],
    ];

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
        if ($request->isCreateOrAttachRequest()) {
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
            return [
                Select::make('Form ID', 'form_id')
                    ->options($formIdOptions)
                    ->rules('required')
                    ->help('Seleziona il tipo di UGC che vuoi creare. Dopo il salvataggio, potrai inserire tutti i dettagli.'),
            ];
        }

        $parentFields = parent::fields($request);

        if ($this->form_id == 'poi') {
            array_splice($parentFields, array_search('user', array_column($parentFields, 'name')), 0, [Text::make('Poi Type', 'raw_data->waypointtype')->onlyOnDetail()]);
        }

        if (empty(static::$activeFields)) {
            return array_merge($parentFields, $this->additionalFields($request));
        }

        $fields = array_filter($parentFields, function ($field) {
            return in_array($field->name, static::$activeFields);
        });

        return array_merge($fields, $this->additionalFields($request));
    }


    public function additionalFields(Request $request)
    {
        return [
            Text::make('Form ID', 'form_id')->resolveUsing(function ($value) {
                if ($this->raw_data and isset($this->raw_data['id'])) {
                    return $this->raw_data['id'];
                } else {
                    return $value;
                }
            })
                ->hideWhenCreating()
                ->hideWhenUpdating(),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 5,
                'maxZoom' => 14,
                'defaultZoom' => 5
            ])->hideFromIndex(),
            $this->getFormDataField(),
            $this->getDeviceDataField(),
            $this->getNominatimField(),
            $this->getRawDataField(),
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
        $parentFilters = parent::filters($request);
        return array_merge($parentFilters, [
            (new UgcFormIdFilter()),
        ]);
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
            (new DownloadUgcCsv()),
            (new DownloadFeatureCollection())->canSee(function ($request) {
                return true;
            }),
            (new CheckUserNoMatchAction)->canRun(function () {
                return true;
            })->standalone(),
            (new UploadAndAssociateUgcMedia())->canSee(function ($request) {
                if ($this->user_id)
                    return auth()->user()->id == $this->user_id && $this->validated === UgcValidatedStatus::NotValidated;
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
            (new DeleteUgcMedia($this->model()))->canSee(function ($request) {
                if ($this->user_id)
                    return auth()->user()->id == $this->user_id && $this->validated === UgcValidatedStatus::NotValidated;
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
