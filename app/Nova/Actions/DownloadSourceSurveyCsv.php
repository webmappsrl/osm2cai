<?php

namespace App\Nova\Actions;

use App\Exports\SourceSurveysExport;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class DownloadSourceSurveyCsv extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Download CSV";

    public $showOnIndex = true;
    public $withoutConfirmation = true;

    public static $chunkCount = 2000;

    /** 
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $fileName = 'source-survey-' . now()->format('Y-m-d') . '.csv';

        Excel::store(new SourceSurveysExport($models), $fileName, 'public');

        $url = Storage::disk('public')->url($fileName);

        return Action::download($url, $fileName);
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
