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

        /**
         * @var \App\Services\OsmService
         */
        $service = app()->make(OsmService::class);
        $models->map( function( $model ) use ($service) {
            if ($model->osm2cai_status ==4)
                return Action::danger('The SDA must be less than 4!');
            $service->updateHikingRouteModelWithOsmData($model);
            //rifattorizzazione dei settori
            $model->computeAndSetSectors();
        });

        $count = $models->count();
        if ( $count == 1 )
        {
            $modelId = $models->first()->id;
            return Action::redirect('/resources/hiking-routes/' . $modelId);
        }

        return Action::message("Percorsi aggiornati con successo!");
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
