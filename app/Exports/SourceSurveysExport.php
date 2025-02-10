<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

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
            $modelLat = DB::select("SELECT ST_Y(geometry) AS latitude FROM ugc_pois WHERE id = $model->id")[0]->latitude;
            $modelLon = DB::select("SELECT ST_X(geometry) AS longitude FROM ugc_pois WHERE id = $model->id")[0]->longitude;

            $lon = $modelLon ?? $rawData['position']['longitude'] ?? '/';
            $lat = $modelLat ?? $rawData['position']['latitude'] ?? '/';

            return [
                'osm2cai_id' => $model->id,
                'operator' => $model->user->name ?? $model->user_no_match ?? '/',
                'monitoring_date' => $rawData['date'] ?? $model->created_at,
                'url' => url("resources/source-surveys/$model->id"),
                'lon' => $lon,
                'lat' => $lat,
                'ele' => $rawData['position']['altitude'] ?? '/',
                'name' => $model->name ?? '/',
                'active' => $rawData['active'] ?? '/',
                'validated' => $model->validated ?? '/',
                'water_flow_rate_validated' => $model->water_flow_rate_validated ?? '/',
                'flow_rate_fill_time' => $rawData['range_time'] ?? '/',
                'flow_rate_volume' => $rawData['range_volume'] ?? '/',
                'flow_rate L/s' => $rawData && isset($rawData['range_volume']) && isset($rawData['range_time']) && is_numeric($rawData['range_volume']) && is_numeric($rawData['range_time']) && $rawData['range_time'] != 0 ? round($rawData['range_volume'] / $rawData['range_time'], 3) : '/',
                'temperature' => $rawData['temperature'] ?? '/',
                'conductivity' => $rawData['conductivity'] ?? '/',
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
            'Validated',
            'Water Flow Rate Validated',
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
