<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ValidateHkingRoute extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnDetail = true;
    public $showOnTableRow = true;
    public $exceptOnIndex = true;

    public $name='Validate';

    public $model;

    function __construct($model = null)
    {
        
        $this->model = $model;
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            try {
                return Action::message('It worked!' . $model->id);
            } catch (\Exception $e) {
                Log::error('An error occurred during the validate operation: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            // LeafletMap::make('Mappa')
            //     ->type('GeoJson')
            //     ->geoJson(json_encode($this->model->getEmptyGeojson()))
            //     ->center($this->model->getCentroid()[1], $this->model->getCentroid()[0])
            //     ->zoom(12)
            //     ->hideFromIndex(),
        ];
    }
}
