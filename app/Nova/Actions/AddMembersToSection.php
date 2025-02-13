<?php

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Nova\Multiselect\Multiselect;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddMembersToSection extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Aggiungi membri alla sezione';

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
        foreach ($models as $model) {
            if (!$user->canManageSection($model)) {
                return Action::danger('Non sei autorizzato a modificare questa sezione');
            }
            $ids = explode(',', str_replace(['[', ']', '"'], '', $fields->users));
            foreach ($ids as $id) {
                $id = trim($id);
                $user = User::find($id);
                if ($user) {
                    $user->section_id = $model->id;
                    $user->save();
                }
            }
        }
        return Action::message('Membri aggiunti alla sezione');
    }

    private function userCanManageSection($user, $section)
    {
        return $user->is_administrator || $user->is_national_referent || ($user->getTerritorialRole() == 'regional' && $user->region_id == $section->region_id) || (!is_null($user->managedSection) && $user->managedSection->id == $section->id);
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $users = User::all()->pluck('name', 'id');
        return [Multiselect::make('Utente', 'users')->options($users)];
    }
}
