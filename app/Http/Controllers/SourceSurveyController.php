<?php

namespace App\Http\Controllers;

use App\Models\UgcPoi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SourceSurveyCollection;

class SourceSurveyController extends Controller
{
    public function overlayGeojson()
    {
        $sourceSurveys = UgcPoi::where('form_id', 'water')->where('validated', 'valid')->get();

        $output = [
            'type' => 'FeatureCollection',
            'features' => []
        ];

        foreach ($sourceSurveys as $sourceSurvey) {
            $rawData = json_decode($sourceSurvey->raw_data, true);
            $date = $rawData['date'] ?? 'N/A';
            if ($date !== 'N/A') {
                $date = Carbon::parse($date)->format('d-m-Y');
            }
            $flowRate = str_replace(',', '.', $sourceSurvey->flow_rate);
            $conductivity = str_replace(',', '.', $sourceSurvey->conductivity) . ' microS/cm';

            $htmlString = <<<HTML
    <div style='font-size: 1.1em; line-height: 1.4em;'>
        <strong>Data del monitoraggio:</strong> <span style='white-space: pre-wrap;'>$date,</span><br>
        <strong>Portata:</strong> <span style='white-space: pre-wrap;'>$flowRate,</span><br>
        <strong>Temperatura:</strong> <span style='white-space: pre-wrap;'>$sourceSurvey->temperature,</span><br>
        <strong>Conducibilitá elettrica:</strong> <span style='white-space: pre-wrap;'>$conductivity,</span><br>
    </div>
    HTML;
            $output['features'][] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $sourceSurvey->id,
                    'popup' => [
                        'html' => $htmlString
                    ]
                ],
                'geometry' => json_decode(DB::select("select st_asGeojson(geometry) as geom from ugc_pois where id=$sourceSurvey->id;")[0]->geom, true),

            ];
        }

        return $output;
    }
}
