<?php

namespace App\Nova\Actions;

use App\Models\UgcPoi;
use App\Models\UgcTrack;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Storage;


class DeleteUgcMedia extends Action
{
    use Queueable;

    public $name = 'Elimina Immagine';
    public $showOnDetail = true;
    public $showOnTableRow = true;

    public $model;

    function __construct($model = null)
    {
        $this->model = $model;

        if (!is_null($resourceId = request('resourceId'))) {
            //get base class name
            $modelClass = class_basename($this->model);
            $this->model = app('App\Models\\' . $modelClass)->find($resourceId);
        }
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $ugcPoi = $models->first();

        if (auth()->user()->id !== $ugcPoi->user_id) {
            return Action::danger('Non sei autorizzato a eliminare immagini per questo UgcPoi.');
        }

        $ugcMediaId = $fields->ugc_media_id;

        $ugcMedia = \App\Models\UgcMedia::find($ugcMediaId);
        if (!$ugcMedia) {
            return Action::danger('Immagine non trovata.');
        }

        try {

            Storage::disk('public')->delete($ugcMedia->relative_url);

            $ugcPoi->ugc_media()->detach($ugcMediaId);

            $ugcMedia->delete();

            return Action::message('Immagine eliminata con successo!');
        } catch (\Exception $e) {
            return Action::danger('Errore durante l\'eliminazione dell\'immagine: ' . $e->getMessage());
        }
    }

    public function fields()
    {
        $medias = $this->model->ugc_media()->get();
        $options = $medias->pluck('id', 'id');
        return [
            // Campo per selezionare l'immagine da eliminare
            Select::make('Immagine', 'ugc_media_id')
                ->options($options)
                ->rules('required')
                ->help('Seleziona l\'id dell\'immagine da eliminare.')
        ];
    }
}
