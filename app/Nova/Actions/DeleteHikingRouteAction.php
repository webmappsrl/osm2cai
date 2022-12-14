<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;

class DeleteHikingRouteAction extends DestructiveAction
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $showOnTableRow = true;
    public $onlyOnIndex = true;

    public $name='ELIMINA';

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
        if (($user->is_national_referent == false) or ($user->is_administrator == false))
            return Action::danger('You do not have permissions to delete this Hiking Route');

        foreach ($models as $m){
            if(!$m->deleted_on_osm)
                return Action::danger('You can not delete this Hiking Route');
            else {
                $m->regions()->sync([]);
                $m->provinces()->sync([]);
                $m->areas()->sync([]);
                $m->sectors()->sync([]);
                $m->save();
                $m->delete();
            }
        }
        return Action::redirect('/resources/hiking-routes');
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
