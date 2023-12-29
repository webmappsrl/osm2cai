<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Text;

class RevertValidateHikingRouteAction extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $showOnDetail = true;

    public $name = 'REVERT VALIDATION';

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
            return Action::danger('Informazioni User non disponibili');

        $permission = $user->getPermissionString();
        $model = $models->first();

        if ($model->osm2cai_status != 4)
            return Action::danger('Lo SDA non è 4!');

        if (!$user->canManageHikingRoute($model))
            return Action::danger('Non hai i permessi su questo percorso');

        $sectors = $model->sectors;
        $areas = $model->areas;
        $provinces = $model->provinces;

        $authorized = false;

        if ($permission == 'Superadmin' || $permission == 'Referente nazionale') {
            $authorized = true;
        } elseif ($permission == 'Referente regionale' && $model->regions->pluck('id')->contains(auth()->user()->region->id)) {
            $authorized = true;
        } elseif ($permission == 'Referente di zona' && (!$sectors->intersect($user->sectors)->isEmpty() || !$areas->intersect($user->areas)->isEmpty() || !$provinces->intersect($user->provinces)->isEmpty())) {
            $authorized = true;
        }

        if (!$authorized) {
            return Action::danger('Non sei autorizzato ad eseguire questa azione');
        }

        $model->revertValidation();

        return Action::redirect($model->id);
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
