<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use App\Enums\UgcValidatedStatus;
use App\Nova\Filters\ValidatedFilter;
use App\Enums\UgcWaterFlowValidatedStatus;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Filters\WaterFlowValidatedFilter;

class SourceSurvey extends UgcPoi
{
    public static string $group = 'Acqua Sorgente';
    public static $priority = 1;

    public static function label()
    {
        $label = 'Monitoraggi';

        return __($label);
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('form_id', 'water');
    }

    public function authorizedToView(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizeToViewAny(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizedToUpdate(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public static function availableForNavigation(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    /**
     * Array of fields to show.
     *
     * @var array
     */
    protected static $activeFields = ['ID', 'User', 'Updated At'];

    public function fields(Request $request)
    {
        $dedicatedData = $this->getNaturalSpringsData();
        $fields = parent::fields($request);

        $dedicatedFields = [
            Text::make('Flow Rate', function () use ($dedicatedData) {
                return $dedicatedData['waterFlowRange'];
            }),
            Text::make('Conductivity', function () use ($dedicatedData) {
                return $dedicatedData['conductivity'];
            }),
            Text::make('Temperature', function () use ($dedicatedData) {
                return $dedicatedData['temperature'];
            }),
            Boolean::make('Photos', function () use ($dedicatedData) {
                return $dedicatedData['photos'];
            }),
            Select::make('Validated', 'validated')
                ->options(UgcValidatedStatus::cases()),
            Select::make('Water Flow Range Validated', 'water_flow_range_validated')
                ->options(UgcWaterFlowValidatedStatus::cases()),
        ];

        return array_merge($fields, $dedicatedFields);
    }

    public function filters(Request $request)
    {
        return [
            (new ValidatedFilter),
            (new WaterFlowValidatedFilter)
        ];
    }
}
