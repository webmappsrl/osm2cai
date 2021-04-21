<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadShape extends Action {
    use InteractsWithQueue, Queueable;

    public $showOnDetail = false;
    public $showOnIndex = false;
    public $showOnTableRow = true;
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection    $models
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models) {
        $model = $models->first();
        $type = strtolower(last(explode('\\', get_class($model))));
        $id = $model->id;

        return Action::redirect(route('api.shapefile.' . $type, ['id' => $id]));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields() {
        return [];
    }
}
