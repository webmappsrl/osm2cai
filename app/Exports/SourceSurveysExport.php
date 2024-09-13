<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SourceSurveysExport implements FromCollection, WithHeadings
{
    protected $models;

    public function __construct(Collection $models)
    {
        $this->models = $models;
    }

    public function collection()
    {
        return $this->models->map(function ($model) {
            $rawData = is_string($model->raw_data) ? json_decode($model->raw_data, true) : $model->raw_data;
            return [
                'osm2cai_id' => $model->id,
                'operator' => $model->user->name ?? $model->user_no_match ?? '/',
                'monitoring_date' => $rawData['date'],
                'url' => url("resources/source-surveys/$model->id"),
                'lon' => $rawData['position']['longitude'] ?? '/',
                'lat' => $rawData['position']['latitude'] ?? '/',
                'ele' => $rawData['position']['altitude'] ?? '/',
                'name' => $model->name ?? '/',
                'active' => $rawData['active'] ?? '/',
                'flow_rate_fill_time' => $model->flow_rate_fill_time ?? '/',
                'flow_rate_volume' => $model->flow_rate_volume ?? '/',
                'flow_rate L/s' => is_numeric($model->flow_rate_volume) && is_numeric($model->flow_rate_fill_time) && $model->flow_rate_fill_time != 0 ? round($model->flow_rate_volume / $model->flow_rate_fill_time, 3) : '/',
                'temperature' => $model->temperature ?? '/',
                'conductivity' => $model->conductivity ?? '/',
                'photo' => count($model->ugc_media()->get()) > 0 ? 'yes' : 'no',
                'notes' => $model->note ?? '/',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Osm2cai_id',
            'Operator',
            'Monitoring date',
            'URL osm2cai',
            'Lon',
            'Lat',
            'Ele',
            'Name',
            'Active',
            'Range Time',
            'Range Volume',
            'Range L/s',
            'Temperature °C',
            'Conductivity MicroS/cm',
            'Photo',
            'Notes',
        ];
    }
}
