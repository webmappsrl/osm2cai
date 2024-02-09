<?php

namespace App\Nova\Actions;

use App\Models\User;
use App\Enums\IssueStatus;
use App\Models\HikingRoute;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Nova\Fields\Textarea;

class CreateIssue extends Action
{
    use InteractsWithQueue, Queueable;

    public $model;

    function __construct($model = null)
    {

        $this->model = $model;

        if (!is_null($resourceId = request('resourceId'))) {
            $this->model = HikingRoute::find($resourceId);
        }
    }

    public $name = "PERCORRIBILITA'";

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        //set the user to the current logged in user
        $user = User::find(auth()->user()->id);
        foreach ($models as $hikingRoute) {
            $hikingRoute->issues_status = $fields->issues_status ?? $hikingRoute->issues_status;
            $hikingRoute->issues_description = $fields->issues_description;
            //set the date field to the current date time when the action is performed
            $hikingRoute->issues_last_update = now();
            $hikingRoute->issues_user_id = $user->id ?? $hikingRoute->issues_user_id;
            $hikingRoute->save();
            $chronology = json_decode($hikingRoute->issues_chronology, true) ?? [];
            $chronology[] = [
                'issues_status' => $hikingRoute->issues_status,
                'issues_description' => $hikingRoute->issues_description,
                'issues_last_update' => $hikingRoute->issues_last_update,
                'issues_user' => $user->name ?? $hikingRoute->issues_user->name
            ];
            $hikingRoute->issues_chronology = json_encode($chronology);
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
                ->displayUsingLabels()
                ->rules('required')
                ->default($this->model->issues_status ?? null),
            Textarea::make('Issues Description')
                ->default($this->model->issues_description ?? null)
                ->nullable()

        ];
    }
}
