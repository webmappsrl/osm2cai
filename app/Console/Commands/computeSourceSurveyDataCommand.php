<?php

namespace App\Console\Commands;

use App\Enums\UgcWaterFlowValidatedStatus;
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
            $rawData = $sourceSurvey->raw_data;

            $volume = $rawData['range_volume'] ?? '0';
            $volume = preg_replace('/[^0-9,]/', '', $volume);
            $volume = str_replace(',', '.', $volume);
            $volume = floatval($volume);

            $time = $rawData['range_time'] ?? '0';
            $time = preg_replace('/[^0-9,]/', '', $time);
            $time = str_replace(',', '.', $time);
            $time = floatval($time);

            $waterFlowRate = $sourceSurvey->water_flow_rate_validated === UgcWaterFlowValidatedStatus::Valid  && $time > 0 ? round(($volume / $time), 3) : 'N/A';
            $conductivity = $rawData['conductivity'] ?? 'N/A';
            $temperature = $rawData['temperature'] ?? 'N/A';

            if (strpos($temperature, '°') === false && $temperature !== 'N/A') {
                $temperature .= '°';
            }
            $photos = count($sourceSurvey->ugc_media) > 0;
            $date = $rawData['date'] ?? 'N/A';

            if ($date !== 'N/A') {
                $date = Carbon::parse($date);
            }

            // Only update columns if they are empty or null
            if (empty($sourceSurvey->flow_rate) || is_null($sourceSurvey->flow_rate)) {
                $sourceSurvey->flow_rate = $waterFlowRate;
            }
            if (empty($sourceSurvey->flow_rate_volume) || is_null($sourceSurvey->flow_rate_volume)) {
                $sourceSurvey->flow_rate_volume = $rawData['range_volume'] ?? 'N/A';
            }
            if (empty($sourceSurvey->flow_rate_fill_time) || is_null($sourceSurvey->flow_rate_fill_time)) {
                $sourceSurvey->flow_rate_fill_time = $rawData['range_time'] ?? 'N/A';
            }
            if (empty($sourceSurvey->conductivity) || is_null($sourceSurvey->conductivity)) {
                $sourceSurvey->conductivity = $conductivity;
            }
            if (empty($sourceSurvey->temperature) || is_null($sourceSurvey->temperature)) {
                $sourceSurvey->temperature = $temperature;
            }
            $sourceSurvey->has_photo = $photos;

            $sourceSurvey->save();
        }
    }
}
