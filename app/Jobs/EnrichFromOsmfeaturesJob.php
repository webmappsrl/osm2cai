<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnrichFromOsmfeaturesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;
    protected $osmfeaturesData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(object $model, array $osmfeaturesData)
    {
        $this->model = $model;
        $this->osmfeaturesData = $osmfeaturesData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            switch ($this->model) {
                case $this->model instanceof \App\Models\CaiHuts:
                    $this->model->enrich($this->osmfeaturesData, ['osmfeatures_data']);
                    break;
                case $this->model instanceof \App\Models\EcPoi:
                    $this->model->enrich($this->osmfeaturesData, []); //empty array means enrich all (osmfeatures_id, osmfeatures_data and score)
                    break;
                case $this->model instanceof \App\Models\Region:
                    $this->model->enrich($this->osmfeaturesData, ['osmfeatures_id', 'osmfeatures_data']);
                    break;
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}