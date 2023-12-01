<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
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
            // Get the hiking routes by their names
            $hikingRoutes = HikingRoute::whereIn('ref', explode(',', $fields->hiking_routes))->get();

            // Attach the hiking routes to the itinerary
            $model->hikingRoutes()->attach($hikingRoutes);
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
        return [
            Text::make('Hiking Routes', 'hiking_routes')
                ->help('Enter the ref of the hiking routes, separated by commas eg.(446A,G8,3,14')
                ->rules('required'),
        ];
    }
}
