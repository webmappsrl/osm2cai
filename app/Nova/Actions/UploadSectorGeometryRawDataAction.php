<?php

namespace App\Nova\Actions;

use Exception;
use App\Models\HikingRoute;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\File;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class UploadSectorGeometryRawDataAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $onlyOnDetail = true;
    public $name='Aggiorna geometria';

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

        if ($fields->geometry) {
            $content = $fields->geometry->get();
            $geojson = $model->textToGeojson( $content );
            $geom = $model->fileToGeometry($content);


            // if ( $model->geometry == $geom )
            // {
            //     return Action::danger("La geometria che hai caricato è uguale a quella già presente.");
            // }

            $model->geometry = $geom;

            try
            {
                DB::beginTransaction();
                //save sector geometry
                $model->save();


                $hrs = $model->children;


                //iterate over sector hr
                $hrs->map( function($hr) {
                    $hr->computeAndSetTechInfo();
                    $hr->computeAndSetTerritorialUnits();
                    $hr->save();
                } );

                DB::commit();
            }
            catch( Throwable $t)
            {
                DB::rollBack();
                return Action::danger("Impossibile aggiornare la geometry. Qualcosa è andato storto: " . $t->getMessage());
            }

            return Action::message('Geometria aggiornata con successo!');
        }

        return Action::danger("Impossibile aggiornare la geometry. Inserisci un file valido.");


    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('Geometry')->help('Carica un file gpx, kml o geojson per aggiornare la geometria del settore ed aggiornare tutti i dati tecnici dei percorsi interessati (percorsi di questo settore + quelli all\'interno dei settori adiacenti)')
        ];
    }
}
