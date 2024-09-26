<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UgcPoisExport implements FromCollection, WithHeadings
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
                'url' => 'https://osm2cai.cai.it/resources/ugc-pois/' . $model->id,
                'lon' => $rawData['position']['longitude'] ?? '/',
                'lat' => $rawData['position']['latitude'] ?? '/',
                'ele' => $rawData['position']['altitude'] ?? '/',
                'name' => $model->name ?? '/',
                'active' => $rawData['active'] ?? '/',
                'range_time' => $rawData['range_time'] ?? '/',
                'range_volume' => $rawData['range_volume'] ?? '/',
                'temperature' => $rawData['temperature'] ?? '/',
                'conductivity' => $rawData['conductivity'] ?? '/',
                'photo' => isset($rawData['photoKeys']) && count($rawData['photoKeys']) > 0 ? 'yes' : 'no',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'osm2cai_id',
            'URL osm2cai',
            'Lon',
            'Lat',
            'Ele',
            'Name',
            'Active',
            'Range Time',
            'Range Volume',
            'Temperature',
            'Conductivity',
            'Photo'
        ];
    }
}
