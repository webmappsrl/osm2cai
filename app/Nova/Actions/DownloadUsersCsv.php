<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadUsersCsv extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Download utenti CSV";

    public $showOnDetail = false;
    public $showOnIndex = true;
    public $showOnTableRow = false;
    public $onlyOnIndex = true;
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

        $FH = fopen('php://memory', 'w');
        $columns = array('Name', 'Email');
        fputcsv($FH, $columns);
        foreach ($models as $m) {
            fputcsv($FH, [$m->name,$m->email]);
        }
        Storage::put('users.csv',$FH );
        return Action::download(route('api.csv.users'),'users');
        //return response()->download(storage_path().'/app/public/users.csv','users.csv')->deleteFileAfterSend(true);

        //return route('api.csv.users', ['users' =>implode(',',$ids)]);
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
