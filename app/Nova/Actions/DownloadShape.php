<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadShape extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnDetail = false;
    public $showOnIndex = false;
    public $showOnTableRow = true;
    public $withoutConfirmation = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $model = $models->first();
        $name = str_replace(" ", "_", $model->name);
        $ids = $model->sectorsIds();

        Storage::disk('public')->makeDirectory('shape_files/zip');
        chdir('storage/shape_files');
        $command = 'rm zip/' . $name . '.zip';
        exec($command);
        $command = 'ogr2ogr -f "ESRI Shapefile" ' .
            $name .
            '.shp PG:"dbname=\'' .
            config('database.connections.osm2cai.database') .
            '\' host=\'' .
            config('database.connections.osm2cai.host') .
            '\' port=\'' .
            config('database.connections.osm2cai.port') .
            '\' user=\'' .
            config('database.connections.osm2cai.username') .
            '\' password=\'' .
            config('database.connections.osm2cai.password') .
            '\'" -sql "SELECT geometry, id, name FROM sectors WHERE id IN (' .
            implode(',', $ids) .
            ');"';
        exec($command);

        $command = 'zip ' . $name . '.zip ' . $name . '.*';
        exec($command);

        $command = 'mv ' . $name . '.zip zip/';
        exec($command);

        $command = 'rm ' . $name . '.*';
        exec($command);

        return Action::download(Storage::disk('public')->url('shape_files/zip/' . $name . '.zip'), $name . '.zip');
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
