<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MountainGroupsExport implements FromCollection, WithHeadings
{
    protected $models;

    public function __construct(Collection $models)
    {
        $this->models = $models;
    }

    public function collection()
    {
        return $this->models->map(function ($model) {
            $aggregatedData = json_decode($model->aggregated_data, true);
            return [
                'osm2cai_id' => $model->id,
                'name' => $model->name ?? '/',
                'POI Generico' => !isset($aggregatedData['ec_pois_count']) && $aggregatedData['ec_pois_count'] == 0 ? '0' : $aggregatedData['ec_pois_count'],
                'POI Rifugio' => !isset($aggregatedData['cai_huts_count']) && $aggregatedData['cai_huts_count'] == 0 ? '0' : $aggregatedData['cai_huts_count'],
                'Percorsi POI Totali' => !isset($aggregatedData['hiking_routes_count']) && $aggregatedData['hiking_routes_count'] === 0 ? '0' : $aggregatedData['hiking_routes_count'],
                'Attivitá o Esperienze' => !isset($aggregatedData['sections_count']) && $aggregatedData['sections_count'] == 0 ? '0' : $aggregatedData['sections_count'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'osm2cai_id', 'name', 'POI Generico', 'Poi Rifugio', 'Percorsi POI Totali', 'Attivitá o Esperienze'
        ];
    }
}
