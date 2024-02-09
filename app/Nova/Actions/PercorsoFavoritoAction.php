<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Fields\Image;

class PercorsoFavoritoAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'PERCORSO FAVORITO';

    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();

        // Gestione del toggle FAVORITE
        $model->region_favorite = $fields->favorite;
        $model->description_cai_it = $fields->description_cai_it;

        // Gestione FEATURE IMAGE
        if ($fields->feature_image) {
            $path = $fields->feature_image->storeAs('public/feature_images', 'OSM2CAI_featureImage_' . $model->id . '.' . $fields->feature_image->getClientOriginalExtension());
            $model->feature_image = $path;
        }

        $model->save();

        return Action::message('Percorso aggiornato con successo!');
    }

    public function fields()
    {

        $id = $_REQUEST['resourceId'] ?? $_REQUEST['resources'];
        $region_favorite = \App\Models\HikingRoute::find(intval($id))->region_favorite;
        return [
            Boolean::make('Percorso Favorito', 'favorite')
                ->default($region_favorite ?? false),
            Image::make('Immagine', 'feature_image')
                ->help('Per il corretto caricamento usare file con dimensione minore di 2MB'),
            Textarea::make('Descrizione', 'description_cai_it')
                ->rules('max:10000') // Limite di 10.000 caratteri
                ->default(\App\Models\HikingRoute::find(intval($id))->description_cai_it ?? '')
        ];
    }
}
