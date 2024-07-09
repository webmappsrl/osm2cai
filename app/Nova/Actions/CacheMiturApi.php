<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CacheMiturApi extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $modelClass = get_class($models->first());
        $modelClass = class_basename($modelClass);

        foreach ($models as $model) {
            Artisan::call("osm2cai:cache-mitur-abruzzo-api $modelClass {$model->id}");
        }

        //sanitize class name
        if ($modelClass == 'HikingRoute') {
            $modelClass = 'hiking_route';
        }
        if ($modelClass == 'MountainGroups') {
            $modelClass = 'mountain_group';
        }
        if ($modelClass == 'CaiHuts') {
            $modelClass = 'hut';
        }
        if ($modelClass == 'EcPoi') {
            $modelClass = 'poi';
        }
        $miturApi = url('/api/v2/mitur_abruzzo/' . strtolower($modelClass) . '/' . $model->id);

        return Action::redirect($miturApi);
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