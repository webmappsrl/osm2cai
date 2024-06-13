<?php

namespace App\Console\Commands;

use App\Models\UgcPoi;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class computeSourceSurveyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:compute-source-surveys-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute the source survey data from raw data column in ugc_pois database table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        foreach (UgcPoi::where('form_id', 'water')->get() as $sourceSurvey) {
            $data = [];
            $rawData = json_decode($sourceSurvey->raw_data, true);

            $volume = $rawData['range_volume'] ?? '0';
            $volume = preg_replace('/[^0-9,]/', '', $volume);
            $volume = str_replace(',', '.', $volume);
            $volume = floatval($volume);

            $time = $rawData['range_time'] ?? '0';
            $time = preg_replace('/[^0-9,]/', '', $time);
            $time = str_replace(',', '.', $time);
            $time = floatval($time);

            $waterFlowRate = ($time > 0 && $volume > 0) ? round(($volume / $time), 3) : 'N/A';
            $conductivity = $rawData['conductivity'] ?? 'N/A';
            $temperature = $rawData['temperature'] ?? 'N/A';

            if (strpos($temperature, '°') === false && $temperature !== 'N/A') {
                $temperature .= '°';
            }
            $photos = !empty($rawData['storedPhotoKeys']) ? true : false;
            $date = $rawData['date'] ?? 'N/A';

            if ($date !== 'N/A') {
                $date = Carbon::parse($date);
            }

            $sourceSurvey->flow_rate = $waterFlowRate;
            $sourceSurvey->flow_rate_volume = $waterFlowRate == 'N/A' ? $waterFlowRate : round($waterFlowRate / $volume, 4);
            $sourceSurvey->flow_rate_fill_time = $waterFlowRate == 'N/A' ? $waterFlowRate : round($waterFlowRate / $time, 4);
            $sourceSurvey->conductivity = $conductivity;
            $sourceSurvey->temperature = $temperature;
            $sourceSurvey->has_photo = $photos;

            $sourceSurvey->save();
        }
    }
}
