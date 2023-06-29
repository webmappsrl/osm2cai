<?php

namespace App\Nova\Actions;

use App\Models\User;
use App\Enums\IssueStatus;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateIssue extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'CREATE ISSUE';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $user = User::find($fields->issues_user);
        foreach ($models as $hikingRoute) {
            $hikingRoute->issues_status = $fields->issues_status ?? $hikingRoute->issues_status;
            $hikingRoute->issues_description = $fields->issues_description ?? $hikingRoute->issues_description;
            $hikingRoute->issues_last_update = $fields->issues_last_update ?? $hikingRoute->issues_last_update;
            $hikingRoute->issues_user_id = $user->id ?? $hikingRoute->issues_user_id;
            $hikingRoute->save();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Issues Status')
                ->options(IssueStatus::cases())
                ->displayUsingLabels(),
            Text::make('Issues Description'),
            Date::make('Issues Last Update')
                ->default(now()->format('Y-m-d')),
            Select::make('Issues User')->options(User::pluck('name', 'id')),
        ];
    }
}
