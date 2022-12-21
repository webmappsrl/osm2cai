<?php

namespace App\Nova\Actions;

use AWS\CRT\HTTP\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;
use function PHPUnit\Framework\isFalse;
use function PHPUnit\Framework\isTrue;

class DeleteHikingRouteAction extends DestructiveAction
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $onlyOnDetail=true;

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
        if (($user->is_national_referent == true) or ($user->is_administrator == true)){
            foreach ($models as $m){
                if($m->deleted_on_osm) {
                    $m->regions()->sync([]);
                    $m->provinces()->sync([]);
                    $m->areas()->sync([]);
                    $m->sectors()->sync([]);
                    $m->save();
                    $m->delete();
                }
                else {
                    return Action::danger('You can not delete this Hiking Route because it is not deleted from OSM' );
                }
            }
            return Action::redirect('/resources/hiking-routes');
        }
        else{
            return Action::danger('You do not have permissions to delete this Hiking Route');
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
