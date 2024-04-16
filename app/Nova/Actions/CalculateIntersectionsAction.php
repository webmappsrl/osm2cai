<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CalculateIntersectionsAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Calculate Intersections';

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
            //get the class name of the model
            $modelClass = get_class($model);
            $modelClass = class_basename($modelClass);

            Artisan::call('osm2cai:calculate_intersections', [
                'model' => $modelClass,
                'id' => $model->id
            ]);
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
