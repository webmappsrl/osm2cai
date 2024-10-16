<?php

namespace App\Nova;

use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use App\Enums\UgcValidatedStatus;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\BelongsTo;
use App\Nova\Actions\DeleteUgcMedia;
use App\Nova\Filters\UgcAppIdFilter;
use App\Nova\Filters\ValidatedFilter;
use App\Nova\Filters\RelatedUGCFilter;
use App\Nova\Traits\RawDataFieldsTrait;
use App\Nova\Filters\UgcUserNoMatchFilter;
use PosLifestyle\DateRangeFilter\Enums\Config;
use App\Nova\Actions\DownloadFeatureCollection;
use App\Nova\Actions\UploadAndAssociateUgcMedia;
use PosLifestyle\DateRangeFilter\DateRangeFilter;

abstract class AbstractUgc extends Resource
{
    use RawDataFieldsTrait;

    public static string $group = 'Rilievi';

    public static array $search = [
        'id',
        'name',
    ];

    public static $searchRelations = [
        'user' => ['name', 'email'],
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $novaFields = [
            ID::make(__('ID'), 'id')->sortable()->readonly(),
            Text::make('User', function () {
                if ($this->user_id) {
                    return '<a style="text-decoration:none; font-weight:bold; color:teal;" href="/resources/users/' . $this->user_id . '">' . $this->user->name . '</a>';
                } else {
                    return $this->user_no_match ?? 'N/A';
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
                ->default(UgcValidatedStatus::NotValidated)
                ->canSee(function ($request) {
                    //if is an ugcTrack instance return $user->ugc_track_validator
                    if ($this instanceof UgcTrack) {
                        return $request->user()->ugc_track_validator ?? false;
                    } else
                        //handle different form_id for ugcPoi
                        return $request->user()->isValidatorForFormId($this->form_id) ?? false;
                })->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $isValidated = $request->$requestAttribute;
                    $model->$attribute = $isValidated;
                    // logic to track validator and validation date

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

        $formFields = $this->jsonForm('raw_data');

        if (!empty($formFields)) {
            array_push(
                $novaFields,
                $formFields,
            );
        }

        return $novaFields;
    }

    public function filters(Request $request)
    {
        return [
            new RelatedUGCFilter,
            new ValidatedFilter,
            new UgcAppIdFilter,
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
        ];
    }

    public function actions(Request $request)
    {
        return [
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
            }),
            (new DownloadFeatureCollection())->canSee(function ($request) {
                return true;
            }),
        ];
    }

    abstract public function additionalFields(Request $request);
}
