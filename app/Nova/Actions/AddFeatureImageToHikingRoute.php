<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;

class AddFeatureImageToHikingRoute extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'FEATURE IMAGE';
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();

        $path = $fields->feature_image->storeAs('public/feature_images','OSM2CAI_featureImage_'.$model->id.'.'.$fields->feature_image->getClientOriginalExtension());
        $model->feature_image = $path;
        $model->save();
        return Action::message('Immagine caricata con con successo!');

    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('Feature Image')
        ];
    }
}
