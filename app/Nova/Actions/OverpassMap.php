<?php

namespace App\Nova\Actions;

use App\Models\HikingRoute;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\Textarea;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class OverpassMap extends Action
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

    public $name = "CERCA PUNTI DI INTERESSE";

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $overpassQuery = urlencode($fields->overpass_query);
        return Action::openInNewTab("https://overpass-turbo.eu/map.html?Q=" . $overpassQuery);
    }


    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $relationId = $this->model->relation_id;
        if (!empty(auth()->user()->default_overpass_query)) {
            $query = auth()->user()->default_overpass_query;
        } else {
            $query = '[out:xml][timeout:250];
relation(' . $relationId . ');
(
node(around:1000)["natural"="peak"];
node(around:1000)["natural"="saddle"];
node(around:1000)["amenity"="drinking_water"];
node(around:1000)["natural"="cave_entrance"];
node(around:1000)["waterway"="waterfall"];
node(around:1000)["historic"="castle"];
node(around:1000)["historic"="monastery"];
node(around:1000)["historic"="archaeological_site"];
node(around:1000)["historic"="ruins"];
node(around:1000)["amenity"="place_of_worship"];
);
out;
';
        }
        return [
            Textarea::make("Overpass Query", "overpass_query")->rows(15)->help("Inserisci la query Overpass da eseguire. Puoi testare la query su https://overpass-turbo.eu/")
                ->default($query)
        ];
    }
}
