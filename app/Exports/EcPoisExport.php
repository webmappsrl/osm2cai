<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EcPoisExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $models;

    public function __construct(Collection $models)
    {
        $this->models = $models;
    }

    public function collection()
    {
        return $this->models->map(function ($model) {
            $tags = json_decode($model->tags, true);
            $osmtype = $model->osm_type == 'N' ? 'node' : ($model->osm_type == 'W' ? 'way' : 'relation');
            $osmUrl = 'https://www.openstreetmap.org/' . $osmtype . '/' . $model->osm_id;
            return [
                'id' => $model->id ?? '/',
                'osm_id' => $model->osm_id ?? '/',
                'osm_type' => $model->osm_type ?? '/',
                'osm_url' => Http::get($osmUrl)->ok() ? $osmUrl : '/',
                'edit_url' => url('/resources/ec-pois/' . $model->id . '/edit'),
                'name' => $model->name ?? '/',
                'amenity' => isset($tags['amenity']) ? $tags['amenity'] : '/',
                'historic' => isset($tags['historic']) ? $tags['historic'] : '/',
                'building' => isset($tags['building']) ? $tags['building'] : '/',
                'water' => isset($tags['water']) ? $tags['water'] : '/',
                'natural' => isset($tags['natural']) ? $tags['natural'] : '/',
                'tourism' => isset($tags['tourism']) ? $tags['tourism'] : '/',
                'elevation' => isset($tags['ele']) ? $tags['ele'] : '/',
                'man_made' => isset($tags['man_made']) ? $tags['man_made'] : '/',
                'religion' => isset($tags['religion']) ? $tags['religion'] : '/',
                'wikipedia' => isset($tags['wikipedia']) ? $tags['wikipedia'] : '/',
                'wikidata' => isset($tags['wikidata']) ? $tags['wikidata'] : '/',
                'wikimedia_commons' => isset($tags['wikimedia_commons']) ? $tags['wikimedia_commons'] : '/',
                'score' => $model->score ?? '/',
                'feature_type' => $model->type ?? '/',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'id', 'osm_id', 'osm_type', 'osm_url', 'edit_url', 'name', 'amenity', 'historic', 'building', 'water',
            'natural', 'tourism', 'elevation', 'man_made', 'religion', 'wikipedia', 'wikidata',
            'wikimedia_commons', 'score', 'feature_type'
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $columns = ['D', 'E', 'R', 'S', 'T'];

                foreach ($columns as $column) {
                    for ($row = 2; $row <= $highestRow; $row++) {
                        $cellCoordinate = $column . $row;
                        $cellValue = $sheet->getCell($cellCoordinate)->getValue();
                        if (filter_var($cellValue, FILTER_VALIDATE_URL)) {
                            $sheet->getCell($cellCoordinate)->setHyperlink(new Hyperlink($cellValue));
                            $sheet->getStyle($cellCoordinate)->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => '0000FF'], // Blu tipico dei collegamenti ipertestuali
                                    'underline' => 'single'
                                ]
                            ]);
                        }
                    }
                }
            },
        ];
    }
}
