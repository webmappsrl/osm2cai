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
            $medias = $sourceSurvey->ugc_media()->get();
            if (count($medias) === 0) {
                $mediasHtml = <<<HTML
                        <div style="display: flex; justify-content: start;"> 'N/A'
                        HTML;
                $mediasHtml .= <<<HTML
                </div>
                HTML;
            } else {
                $mediasHtml = <<<HTML
                        <div style="display: flex; justify-content: start;">
                        HTML;
                foreach ($medias as $media) {
                    $mediasHtml .= <<<HTML
                <a href="{$media->relative_url}" target="_blank">
                    <img src="{$media->relative_url}" style="width: 60px; margin-right: 5px; height: 60px; border: 1px solid #ccc; border-radius: 40%; padding: 2px;" alt="Thumbnail">
                </a>
                HTML;
                }
                $mediasHtml .= <<<HTML
                </div>
                HTML;
            }
            $osm2caiUrl = url('resources/source-surveys/' . $sourceSurvey->id);

            $rawData = json_decode($sourceSurvey->raw_data, true);
            $date = $rawData['date'] ?? 'N/A';
            if ($date !== 'N/A') {
                $date = Carbon::parse($date)->format('d-m-Y');
            }
            if (isset($sourceSurvey->flow_rate) && $sourceSurvey->flow_rate !== 'N/A') {
                $flowRate = str_replace(',', '.', $sourceSurvey->flow_rate) . ' L/s';
            } else {
                $flowRate = 'N/A';
            }
            if (isset($sourceSurvey->temperature) && $sourceSurvey->temperature !== 'N/A') {
                $temperature = str_replace(',', '.', $sourceSurvey->temperature) . 'C';
            } else {
                $temperature = 'N/A';
            }
            if (isset($sourceSurvey->conductivity) && $sourceSurvey->conductivity !== 'N/A') {
                $conductivity = str_replace(',', '.', $sourceSurvey->conductivity) . ' microS/cm';
            } else {
                $conductivity = 'N/A';
            }

            if (isset($rawData['active'])) {
                switch ($rawData['active']) {
                    case 'yes':
                        $isActive = 'SI';
                        break;
                    case 'no':
                        $isActive = 'NO';
                        break;
                    default:
                        $isActive = 'N/A';
                        break;
                }
            } else {
                $isActive = 'N/A';
            }

            $htmlString = <<<HTML
<div style='font-size: 1.1em; line-height: 1.4em;'>
    <strong>ID:</strong> <span style='white-space: pre-wrap;'>$sourceSurvey->id</span><br>
    <strong>Data del monitoraggio:</strong> <span style='white-space: pre-wrap;'>$date</span><br>
    <strong>Sorgente Attiva:</strong> <span style='white-space: pre-wrap;'>$isActive</span><br>
    <strong>Portata:</strong> <span style='white-space: pre-wrap;'>$flowRate</span><br>
    <strong>Temperatura:</strong> <span style='white-space: pre-wrap;'>$temperature</span><br>
    <strong>Conducibilitá elettrica:</strong> <span style='white-space: pre-wrap;'>$conductivity</span><br>
    $mediasHtml <br>
    <a href="$osm2caiUrl" target="_blank" style="text-decoration: underline;">Vedi su OSM2CAI</a>
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
