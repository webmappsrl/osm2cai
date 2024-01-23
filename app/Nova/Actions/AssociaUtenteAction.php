<?php

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

class AssociaUtenteAction extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Associa utente';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $link = url('/resources/users/' . $fields->user_id);

        return Action::redirect($link);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $users = User::all()->pluck('name', 'id');
        return [Select::make('Utente', 'user_id')->options($users)->displayUsingLabels()->searchable()];
    }
}
