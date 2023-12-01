<?php

namespace App\Nova\Actions;

use App\Nova\HikingRoute;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BelongsToMany;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddHikingRoutesToItinerary extends Action
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
        foreach ($models as $model) {
            $model->hikingRoutes()->syncWithoutDetaching($fields->hikingRoutes);
        }

        return Action::message('Hiking routes added successfully!');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        // return [
        //     BelongsToMany::make('Hiking Routes', 'hikingRoutes', HikingRoute::class)->searchable(),
        // ];
    }
}
