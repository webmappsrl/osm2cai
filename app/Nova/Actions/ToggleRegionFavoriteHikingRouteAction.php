<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ToggleRegionFavoriteHikingRouteAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name='FAVORITE (TOGGLE)';


    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $user = auth()->user();
        if (!$user && $user == null)
            return Action::danger('User info is not available');

        foreach($models as $model) {
            if($user->canManageHikingRoute($model)) {
                $model->region_favorite=!$model->region_favorite;
                $model->save();    
            } 
            else {
                return Action::danger('You don\'t have the permission. Ask to the administrator.');
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
        return [];
    }
}
