<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Date;

class ValidatedHrCsvDownload extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $hr = HikingRoute::where('osm2cai_status', 4)
            ->where('osm2cai_validated_at', $fields->data)
            ->get();

        $filename = 'validated_hr_' . $fields->data . '.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('id', 'name', 'osm_id', 'osm2cai_status', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment', 'osm2cai_validated_at', 'osm2cai_validated_by', 'osm2cai_validated_comment'));

        foreach ($hr as $row) {
            fputcsv($handle, array($row['id'], $row['name'], $row['osm_id'], $row['osm2cai_status'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment'], $row['osm2cai_validated_at'], $row['osm2cai_validated_by'], $row['osm2cai_validated_comment']));
        }

        fclose($handle);

        return Action::download($filename);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Date::make('Data')->format('YYYY-MM-DD'),
        ];
    }
}
