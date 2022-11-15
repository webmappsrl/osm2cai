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

    public $name='SYNC WITH OSM DATA';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {

        $models->map( function( $model ) {
            $this->updateModelWithOsmData($model);
        } );

        $count = $models->count();
        if ( $count == 1 )
        {
            $modelId = $models->first()->id;
            return Action::redirect('/resources/hiking-routes/' . $modelId);
        }

        return Action::message("Percorsi aggiornati con successo!");
    }

    public function updateModelWithOsmData( HikingRoute $model )
    {
        $relationId = $model->relation_id;

        /**
         * @var \App\Services\OsmService
         */
        $service = OsmService::getService();

        $osmHr = $service->getHikingRoute( $relationId );
        $osmGeo = $service->getHikingRouteGeometry( $relationId );

        if ( $osmGeo !== $model->geometry )
        {
            $model->geometry = $osmGeo;
        }

        foreach ( $osmHr as $attribute => $val )
        {
            $model->$attribute = $val;
        }

        $model->save();
        $model->computeAndSetTechInfo();
        //$model->computeAndSetTerritorialUnits();//it doesnt work

        return $model->save();
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
