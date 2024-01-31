<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use App\Services\OsmService;
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

class OsmSyncHikingRouteAction extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $showOnDetail = true;

    public $name = 'SYNC WITH OSM DATA';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        /**
         * @var \App\Services\OsmService
         */
        $service = app()->make(OsmService::class);
        $permission = auth()->user()->getPermissionString();

        foreach ($models as $model) {
            if ($model->osm2cai_status > 3)
                return Action::danger('"Per poter effetturare la sincronizzazione forzata con OpenStreetMap è necessarrio che il percorso abbia uno Stato di accatastamento minore o uguale a 3; se necessario procedere prima con REVERT VALIDATION"
');
            $sectors = $model->sectors;
            $areas = $model->areas;
            $provinces = $model->provinces;
            if ($permission == 'Superadmin' || $permission == 'Referente nazionale') {
                $service->updateHikingRouteModelWithOsmData($model);
            }
            if ($permission == 'Referente regionale') {
                if ($model->regions->pluck('id')->contains(auth()->user()->region->id)) {
                    $service->updateHikingRouteModelWithOsmData($model);
                } else {
                    return Action::danger('Non sei autorizzato ad eseguire questa azione');
                }
            }
            if ($permission == 'Referente di zona') {
                if (!$sectors->intersect(auth()->user()->sectors)->isEmpty()) {
                    $service->updateHikingRouteModelWithOsmData($model);
                } else if (!$areas->intersect(auth()->user()->areas)->isEmpty()) {
                    $service->updateHikingRouteModelWithOsmData($model);
                } else if (!$provinces->intersect(auth()->user()->provinces)->isEmpty()) {
                    $service->updateHikingRouteModelWithOsmData($model);
                } else {
                    return Action::danger('Non sei autorizzato ad eseguire questa azione');
                }
            }
        }
        return Action::redirect('/resources/hiking-routes/' . $model->id);
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
