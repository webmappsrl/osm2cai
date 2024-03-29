<?php

namespace App\Console\Commands;

use App\Models\EcPoi;
use Illuminate\Console\Command;

class UpdateEcPoisScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:update-ec-pois-score {?id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loop over all ec pois and update the score column';

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
        if ($this->argument('id')) {
            $poi = EcPoi::where('id', $this->argument('id'))->get();
            $tags = json_decode($poi->tags, true);
            $score = 1;

            if (isset($tags['wikipedia'])) {
                $score += 1;
            }
            if (isset($tags['wikimedia_commons'])) {
                $score += 1;
            }
            if (isset($tags['wikidata'])) {
                $score += 1;
            }
            if (isset($tags['ele'])) {
                $score += 1;
            }
            if (isset($tags['contact:website']) || isset($tags['source']) || isset($tags['website'])) {
                $score += 1;
            }

            $poi->score = $score;
            $poi->save();
        } else {
            $ecPois = EcPoi::all();

            foreach ($ecPois as $poi) {
                $tags = json_decode($poi->tags, true);
                $score = 1;

                if (isset($tags['wikipedia'])) {
                    $score += 1;
                }
                if (isset($tags['wikimedia_commons'])) {
                    $score += 1;
                }
                if (isset($tags['wikidata'])) {
                    $score += 1;
                }
                if (isset($tags['ele'])) {
                    $score += 1;
                }
                if (isset($tags['contact:website']) || isset($tags['source']) || isset($tags['website'])) {
                    $score += 1;
                }

                $poi->score = $score;
                $poi->save();
            }
        }
    }
}
