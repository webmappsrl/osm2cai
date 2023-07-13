<?php

namespace App\Console\Commands;

use App\Models\Section;
use App\Models\HikingRoute;
use Illuminate\Console\Command;

class HikingRouteSectionRelationSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relationships:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync relationships between hiking routes and sections';

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
        $this->info('[START] Sync relationships between hiking routes and sections');


        $sections = Section::all();

        //HikingRoute has a source_ref field that is the same as the cai_code field in Section, so we can use that to match them
        foreach ($sections as $section) {
            $this->info('Syncing section ' . $section->name . ' with hiking route source ref: ' . $section->cai_code);
            try {
                $hikingRoute = HikingRoute::where('source_ref', $section->cai_code)->first();
            } catch (\Exception $e) {
                $this->error('Error syncing section ' . $section->name . ' with hiking route source ref: ' . $section->cai_code);
                $this->error($e->getMessage());
            }
            if ($hikingRoute) {
                $hikingRoute->sections()->syncWithoutDetaching($section->id);
                $this->info('Synced section ' . $section->name . ' with hiking route source ref: ' . $section->cai_code) . PHP_EOL;
            }
        }

        $this->info('[END] Sync relationships between hiking routes and sections');
    }
}
