<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Text;
use App\Http\Facades\OsmClient;
use App\Models\Itinerary;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ImportItinerary extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = 'Crea itinerario ed Associa Percorsi';

    public function handle(ActionFields $fields, Collection $models)
    {
        if ($fields['ids']) {
            $ids = explode(',', $fields->ids);
        } else {
            return Action::danger('No IDs provided.');
        }
        if (!$fields['itinerary_name']) {
            return Action::danger('No itinerary name provided.');
        }
        $itinerary = Itinerary::firstOrCreate([
            'name' => $fields->itinerary_name,
        ]);

        try {
            if ($fields['import_source'] == 'OSM') {
                $hikingRoutes = DB::table('hiking_routes')->whereIn('relation_id', $ids)->get();
                $hikingRoutesIds = $hikingRoutes->pluck('id')->toArray();
                $itinerary->hikingRoutes()->attach($hikingRoutesIds);
                return Action::message('Itinerario creato con successo!');
            } else if ($fields['import_source'] == 'OSM2CAI') {
                $hikingRoutes = DB::table('hiking_routes')->whereIn('id', $ids)->get();
                $itinerary->hikingRoutes()->attach($ids);
                return Action::message('Itinerario creato con successo!');
            } else {
                return Action::danger('Invalid import source.');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Action::danger('Errore durante la creazione dell\'itinerario.');
        }
    }

    public function fields()
    {
        return [
            Select::make('Sorgente', 'import_source')->options([
                'OSM' => 'OpenStreetMap',
                'OSM2CAI' => 'OSM2CAI',
            ])->rules('required'),

            Text::make('IDs dei percorsi', 'ids')->rules('required')->help('Comma separated IDs e.g. 123,456,789'),
            Text::make('Nome Itinerario', 'itinerary_name')
                ->help('Nome dell\'itinerario da creare')
        ];
    }
}
