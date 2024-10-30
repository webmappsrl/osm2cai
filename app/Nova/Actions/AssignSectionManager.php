<?php

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Date;

class AssignSectionManager extends Action
{
    use InteractsWithQueue, Queueable;

    public $name;

    public function __construct()
    {
        $this->name = __('Assegna responsabile sezione');
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            $user = User::find($fields->sectionManager);
            $user->manager_section_id = $model->id;
            $user->section_manager_expire_date = $fields->section_manager_expire_date;
            $user->save();
            $model->save();
        }

        return Action::message(__('Responsabile sezione assegnato con successo'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make(__('Responsabile sezione'), 'sectionManager')
                ->options(User::all('id', 'name')->pluck('name', 'id'))
                ->searchable(),
            Date::make(__('Data di scadenza dell\'incarico'), 'section_manager_expire_date')
                ->nullable(),
        ];
    }
}
