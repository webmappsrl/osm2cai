<?php

namespace App\Nova;

use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Boolean;
use App\Enums\UgcValidatedStatus;
use Wm\MapPointNova3\MapPointNova3;
use App\Nova\Filters\ValidatedFilter;
use App\Enums\UgcWaterFlowValidatedStatus;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Filters\WaterFlowValidatedFilter;
use Laravel\Nova\Fields\Textarea;

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
        $query =  $query->where('form_id', 'water');
    }


    public function authorizedToUpdate(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizeToUpdate(Request $request)
    {
        return $request->user()->is_source_validator;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * Array of fields to show.
     *
     * @var array
     */
    protected static $activeFields = ['ID', 'User'];

    public function fields(Request $request)
    {
        $fields = parent::fields($request);

        $dedicatedFields = [
            Date::make('Monitoring Date', 'updated_at')
                ->sortable(), //same data as raw_data['date']
            Text::make('Flow Rate L/s', 'flow_rate')->resolveUsing(function ($value) {
                if ($this->water_flow_rate_validated === UgcWaterFlowValidatedStatus::Valid) {
                    //extract values and replace comma with dot. if dot is found, do not replace. the fina result should be a float value with point
                    if (strpos($this->flow_rate_volume, '.') !== false) {
                        $volume = $this->flow_rate_volume;
                    } else {
                        $volume = preg_replace('/[^0-9,]/', '', $this->flow_rate_volume);
                    }
                    if (strpos($this->flow_rate_fill_time, '.') !== false) {
                        $time = $this->flow_rate_fill_time;
                    } else {
                        $time = preg_replace('/[^0-9,]/', '', $this->flow_rate_fill_time);
                    }
                    $volume = str_replace(',', '.', $volume);
                    $time = str_replace(',', '.', $time);

                    if (is_numeric($volume) && is_numeric($time) && $time != 0) {
                        return round($volume / $time, 3);
                    } else {
                        return null;
                    }
                }
            }),

            Text::make('Flow Rate/Volume', 'flow_rate_volume')->hideFromIndex(),
            Text::make('Flow Rate/Fill Time', 'flow_rate_fill_time')->hideFromIndex(),
            Text::make('Conductivity microS/cm', 'conductivity'),
            Text::make('Temperature °C', 'temperature'),
            Boolean::make('Photos', 'has_photo')->hideFromDetail(),
            Select::make('Validated', 'validated')
                ->options(UgcValidatedStatus::cases()),
            Select::make('Water Flow Rate Validated', 'water_flow_rate_validated')
                ->options(UgcWaterFlowValidatedStatus::cases()),
            MapPointNova3::make('geometry')->withMeta([
                'center' => [42, 10],
                'attribution' => '<a href="https://webmapp.it/">Webmapp</a> contributors',
                'tiles' => 'https://api.webmapp.it/tiles/{z}/{x}/{y}.png',
                'minZoom' => 8,
                'maxZoom' => 17,
                'defaultZoom' => 13
            ])->hideFromIndex(),
            Text::make('Gallery', function () {
                //get the ugc_media related to the resource
                $medias = $this->ugc_media()->get();
                $html = <<<HTML
                        <div style="display: flex; justify-content: start;">
                        HTML;
                foreach ($medias as $media) {
                    $html .= <<<HTML
                <a href="{$media->relative_url}" target="_blank">
                    <img src="{$media->relative_url}" style="width: 60px; margin-right: 5px; height: 60px; border: 1px solid #ccc; border-radius: 40%; padding: 2px;" alt="Thumbnail">
                </a>
                HTML;
                }
                $html .= <<<HTML
                </div>
                HTML;

                return $html;
            })->asHtml()
                ->onlyOnDetail(),
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
        return [
            Text::make('ID', 'id')->hideFromIndex()->readonly(),
            Text::make('User', 'user')->resolveUsing(function ($user) {
                return $user->name ?? $this->user_no_match;
            })->readonly(),
            Date::make('Monitoring Date', 'updated_at')
                ->sortable()->readonly(), //same data as raw_data['date']
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
                ->options(UgcValidatedStatus::cases()),
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
