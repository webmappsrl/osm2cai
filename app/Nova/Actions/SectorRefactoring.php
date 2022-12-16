<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SectorRefactoring extends Action
{
    use InteractsWithQueue, Queueable;

    public $name='REFATTORIZZAZIONE SETTORI';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $user = auth()->user();
        $date = Carbon::now();

        if (!$user && $user == null)
            return Action::danger('User info is not available');

        $model = $models->first();
        if (!$user->canManageHikingRoute($model))
            return Action::danger('You don\'t have permissions on this Hiking Route');
        $model->computeAndSetSectors();

        return Action::redirect($model->id);
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
