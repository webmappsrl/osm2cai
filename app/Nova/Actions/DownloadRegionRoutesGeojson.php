<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadRegionRoutesGeojson extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Download percorsi Geojson";

    public $showOnDetail = true;
    public $showOnIndex = false;
    public $showOnTableRow = true;
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param Collection $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();
        $type = strtolower(last(explode('\\', get_class($model))));
        $id = $model->id;
        $name = $model->name;

        return Action::download(route('api.geojson_complete.' . $type, ['id' => $id]), $name . '.geojson');
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
