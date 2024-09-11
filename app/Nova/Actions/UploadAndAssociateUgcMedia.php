<?php

namespace App\Nova\Actions;

use Ebess\AdvancedNovaMediaLibrary\Fields\Images;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class UploadAndAssociateUgcMedia extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Carica Immagine';
    public $showOnDetail = true;
    public $showOnTableRow = true;

    public function handle(ActionFields $fields, Collection $models)
    {

        $ugcPoi = $models->first();

        if (auth()->user()->id !== $ugcPoi->user_id) {
            return Action::danger('Non sei autorizzato a caricare immagini per questo UgcPoi.');
        }

        if (!$fields->has('ugc-media')) {
            return Action::danger('Nessuna immagine trovata nella richiesta.');
        }

        $ugcMedia = $fields->ugc_media;

        if (!$ugcMedia) {
            return Action::danger('L\'immagine caricata è nulla.');
        }

        \Log::info('Image details:', [
            'name' => $ugcMedia->getClientOriginalName(),
            'size' => $ugcMedia->getSize(),
            'mime' => $ugcMedia->getMimeType(),
        ]);

        if ($ugcMedia->getSize() > 10485760) {
            return Action::danger('L\'immagine caricata supera le dimensioni massime consentite');
        }

        try {
            $path = $ugcMedia->store('ugc-media', 'public');

            // Modifica qui per includere lo SRID
            $geometry = $ugcPoi->geometry;
            $newUgcMedia = \App\Models\UgcMedia::create([
                'name' => $ugcMedia->getClientOriginalName(),
                'relative_url' => 'ugc-media/' . basename($path),
                'user_id' => auth()->user()->id,
                'geometry' => $geometry,
                'app_id' => 'osm2cai'
            ]);

            $ugcPoi->ugc_media()->attach($newUgcMedia->id);


            return Action::message('Immagine caricata e associata con successo!');
        } catch (\Exception $e) {
            return Action::danger('Errore durante il caricamento dell\'immagine: ' . $e->getMessage());
        }
    }

    public function fields()
    {
        return [
            File::make('Immagine', 'ugc_media')
                ->disk('public')
                ->path('ugc-media')
                ->store(function ($request, $model) {
                    return $request->file('ugc-media')->store('ugc-media', 'public');
                })
                ->help('Carica un\'immagine da associare al POI. Dimensione consentita: max 10MB')
        ];
    }
}
