<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class TerritorialUnit extends Model {
    use HasFactory;

    abstract public function users();

    abstract public function sectorsIds(): array;

    /**
     * Generate a shapefile for the model
     *
     * @return string the shapefile relative url
     */
    public function getShapefile(): string {
        $class = get_class($this);
        $model = $class::find($this->id);
        $name = str_replace(" ", "_", $model->name);
        $ids = $model->sectorsIds();

        Storage::disk('public')->makeDirectory('shape_files/zip');
        chdir(Storage::disk('public')->path('shape_files'));
        if (Storage::disk('public')->exists('shape_files/zip/' . $name . '.zip'))
            Storage::disk('public')->delete('shape_files/zip/' . $name . '.zip');
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

        return 'shape_files/zip/' . $name . '.zip';
    }
}
