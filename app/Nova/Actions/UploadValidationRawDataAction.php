<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Imumz\LeafletMap\LeafletMap;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;

class UploadValidationRawDataAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnDetail = true;
    public $showOnIndex = false;
    public $name='UPLOAD GPX/KML/GEOJSON';
    public $HR;


    public function __construct($HR = null)
    {

        $this->HR = HikingRoute::find($HR); ;

    }

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
            $path = $fields->geometry->storeAs('local',explode('.',$fields->geometry->hashName())[0] . '.' . $fields->geometry->getClientOriginalExtension());
            $content = Storage::get($path);
            $geom = $model->fileToGeometry($content);

            $model->geometry_raw_data = $geom;
            $model->save();
            return Action::message('File caricato e geometria aggiornata con successo!');
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
            File::make('Geometry')
        ];
    }
}
