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
    protected $signature = 'osm2cai:sync_relationships_sections_hiking_routes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize relationships between hiking routes and sections.
This command retrieves all existing sections and iterates through each one. For each section, it searches for the corresponding hiking route using the "source_ref" field. If a matching hiking route is found, the section is associated with the hiking route.

To ensure successful synchronization, verify that the "HikingRoute" and "Section" models have the appropriate "source_ref" and "cai_code" fields defined and that they are consistent with each other.

';


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
            $this->info('Syncing section ' . $section->name . ' with hiking routes source ref: ' . $section->cai_code);

            try {
                $hikingRoutes = HikingRoute::where('source_ref', 'like', '%' . $section->cai_code . '%')->get();
            } catch (\Exception $e) {
                $this->error('Error syncing section ' . $section->name . ' with hiking routes source ref: ' . $section->cai_code);
                $this->error($e->getMessage());
            }

            if ($hikingRoutes->isNotEmpty()) {
                $hikingRoutesId = $hikingRoutes->pluck('id')->toArray();
                $section->hikingRoutes()->sync($hikingRoutesId);
                $this->info('Synced section ' . $section->name . ' with hiking routes source ref: ' . $section->cai_code . PHP_EOL);
            }
        }


        $this->info('[END] Sync relationships between hiking routes and sections');
    }
}
