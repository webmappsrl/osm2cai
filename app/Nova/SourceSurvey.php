<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Illuminate\Support\Carbon;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use App\Enums\UgcValidatedStatus;
use Laravel\Nova\Fields\Textarea;
use Wm\MapPointNova3\MapPointNova3;
use Illuminate\Support\Facades\Auth;
use App\Nova\Filters\ValidatedFilter;
use App\Nova\AbstractValidationResource;
use App\Enums\UgcWaterFlowValidatedStatus;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Filters\WaterFlowValidatedFilter;

class SourceSurvey extends AbstractValidationResource
{
    public static function getFormId(): string
    {
        return 'water';
    }

    public static function getLabel(): string
    {
        return 'Acqua Sorgente';
    }

    public static function getAuthorizationMethod(): string
    {
        return 'is_source_validator';
    }

    /**
     * Array of fields to show.
     *
     * @var array
     */
    protected static $activeFields = ['ID', 'User', 'Validated', 'Validation Date', 'Validator', 'geometry', 'Gallery'];

    public function fields(Request $request)
    {
        $rawData = $this->raw_data;
        $fields = parent::fields($request);

        $dedicatedFields = [
            Date::make('Monitoring Date', function () use ($rawData) {
                return $rawData['date'];
            })->sortable(),
            Text::make('Flow Rate L/s', 'flow_rate')->resolveUsing(function ($value) {
                return $this->calculateFlowRate();
            }),
            Text::make('Flow Rate/Volume', 'flow_rate_volume')->hideFromIndex(),
            Text::make('Flow Rate/Fill Time', 'flow_rate_fill_time')->hideFromIndex(),
            Text::make('Conductivity microS/cm', 'conductivity'),
            Text::make('Temperature °C', 'temperature'),
            Boolean::make('Photos', function () {
                return count($this->ugc_media) > 0;
            })->hideFromDetail(),
            Select::make('Water Flow Rate Validated', 'water_flow_rate_validated')
                ->options(UgcWaterFlowValidatedStatus::cases()),
            Textarea::make('Notes', 'note')->hideFromIndex(),
        ];

        return array_merge($fields, $dedicatedFields);
    }

    public function fieldsForUpdate()
    {
        $readonlyFields = $this->readonlyFields();
        $modifiablesFields = $this->modifiablesFields();
        return array_merge($readonlyFields, $modifiablesFields);
    }

    public function readonlyFields()
    {
        $rawData = $this->raw_data;
        return [
            Text::make('ID', 'id')->hideFromIndex()->readonly(),
            Text::make('User', 'user')->resolveUsing(function ($user) {
                return $user->name ?? $this->user_no_match;
            })->readonly(),
            Date::make('Monitoring Date', function () use ($rawData) {
                return $rawData['date'];
            })
                ->sortable()->readonly(),
        ];
    }
    public function modifiablesFields()
    {
        return [
            Text::make('Flow Rate/Volume', 'flow_rate_volume'),
            Text::make('Flow Rate/Fill Time', 'flow_rate_fill_time'),
            Text::make('Conductivity microS/cm', 'conductivity'),
            Text::make('Temperature °C', 'temperature'),
            Select::make('Validated', 'validated')
                ->options(UgcValidatedStatus::cases())
                ->canSee(function ($request) {
                    return $request->user()->isValidatorForFormId($this->form_id) ?? false;
                })->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
                    $isValidated = $request->$requestAttribute;
                    $model->$attribute = $isValidated;

                    if ($isValidated == UgcValidatedStatus::Valid) {
                        $model->validator_id = $request->user()->id;
                        $model->validation_date = now();
                    } else {
                        $model->validator_id = null;
                        $model->validation_date = null;
                    }
                }),
            Select::make('Water Flow Rate Validated', 'water_flow_rate_validated')
                ->options(UgcWaterFlowValidatedStatus::cases()),
            Textarea::make('Notes', 'note'),

        ];
    }

    public function filters(Request $request)
    {
        return [
            (new ValidatedFilter),
            (new WaterFlowValidatedFilter)
        ];
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
            (new \App\Nova\Actions\DownloadSourceSurveyCsv()),
            (new \App\Nova\Actions\CheckUserNoMatchAction)->canRun(function () {
                return true;
            })->standalone()
        ];
    }
}
