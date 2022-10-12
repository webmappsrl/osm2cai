<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadRegionRoutesCsv extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Download percorsi CSV";

    public $showOnDetail = true;
    public $showOnIndex = false;
    public $showOnTableRow = true;
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();
        $type = strtolower(last(explode('\\', get_class($model))));
        $id = $model->id;
        $name = $model->name;

        return Action::download(route('api.csv.' . $type, ['id' => $id]), $name . '.kml');
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
