<?php

namespace App\Nova\Actions;

use App\Models\User;
use App\Models\Section;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Nova\Multiselect\Multiselect;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Http\Requests\NovaRequest;

class RemoveMembersFromSection extends Action
{
    use InteractsWithQueue, Queueable;

    public $name;

    public function __construct()
    {
        $this->name = __('Rimuovi Membri dalla Sezione');
    }

    /**
     * Esegue l'azione sui modelli selezionati.
     *
     * @param  ActionFields  $fields
     * @param  Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $currentUser = auth()->user();

        foreach ($models as $model) {
            if (!$currentUser->canManageSection($model)) {
                return Action::danger('Non sei autorizzato a modificare questa sezione');
            }

            $ids = json_decode($fields->members);

            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user && $user->section_id == $model->id) {
                    $user->section_id = null;
                    $user->saveQuietly();
                }
            }
        }

        return Action::message(__('Membri rimossi dalla sezione'));
    }

    /**
     * Define i campi disponibili per l'azione.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields()
    {
        $request = request();
        if ($request->resourceId) {
            $section = Section::find($request->resourceId);
            $users = $section ? $section->users()->orderBy('name')->pluck('name', 'id') : collect();
        } else {
            $users = collect();
        }

        return [
            Multiselect::make('Members')->options($users),
        ];
    }
}
