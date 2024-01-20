<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;

class UploadValidationRawDataAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnDetail = true;
    public $showOnIndex = false;
    public $name = 'UPLOAD GPX/KML/GEOJSON';
    public $HR;


    public function __construct($HR = null)
    {

        $this->HR = HikingRoute::find($HR);
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

        if ($model->osm2cai_status > 3) {
            return Action::danger("Per poter effetturare l'upload della traccia rilevata del percorso è necessario che il percorso abbia uno Stato di accatastamento minore o uguale a 3; se necessario procedere prima con REVERT VALIDATION");
        }

        $permission = auth()->user()->getPermissionString();
        $sectors = $model->sectors;
        $areas = $model->areas;
        $provinces = $model->provinces;

        $authorized = false;

        if ($permission == 'Superadmin' || $permission == 'Referente nazionale') {
            $authorized = true;
        } elseif ($permission == 'Referente regionale' && $model->regions->pluck('id')->contains(auth()->user()->region->id)) {
            $authorized = true;
        } elseif ($permission == 'Referente di zona' && (!$sectors->intersect(auth()->user()->sectors)->isEmpty() || !$areas->intersect(auth()->user()->areas)->isEmpty() || !$provinces->intersect(auth()->user()->provinces)->isEmpty())) {
            $authorized = true;
        }

        if (!$authorized) {
            return Action::danger('Non sei autorizzato ad eseguire questa azione');
        }

        if ($fields->geometry) {
            $path = $fields->geometry->storeAs('local', explode('.', $fields->geometry->hashName())[0] . '.' . $fields->geometry->getClientOriginalExtension());
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

        $confirmText = 'ATTENZIONE: il file che verrà caricato servirà esclusivamente per essere confrontato con la traccia presente nel Catasto/OpenStreetMap; in caso di validazione sarà la traccia del Catasto/OpenStreetMap (in mappa di colore blu) ad essere validata.';


        return [
            File::make('Geometry')
                ->help($confirmText)
        ];
    }
}
