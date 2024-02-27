<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use ZipArchive;

class DownloadGeojsonZipUgcTracks extends Action
{
    use Queueable;

    public $name = 'Download Geojson ZIP';

    public function __construct()
    {
        $this->onlyOnIndex();
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        $zip = new ZipArchive;
        $zipFileName = 'OSM2CAI_ugctracks_' . now()->format('Ymd') . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($models as $model) {
                $geojson = $model->getGeojson();
                $filePath = 'ugctracks/' . $model->id . '.geojson';
                Storage::disk('public')->put($filePath, json_encode($geojson));
                $zip->addFile(storage_path('app/public/' . $filePath), $model->id . '.geojson');
            }
            $zip->close();

            // Pulizia dei file temporanei
            foreach ($models as $model) {
                Storage::disk('public')->delete('ugctracks/' . $model->id . '.geojson');
            }

            $publicPath = Storage::disk('public')->url($zipFileName);

            return Action::redirect($publicPath, $zipFileName);
        } else {
            return Action::danger('Impossibile creare il file zip.');
        }
    }

    public function fields()
    {
        return [];
    }
}
