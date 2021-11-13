<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Console\Command;

class setExpectedFromRegionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'osm2cai:set-expected-from-region';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It fills num_expected field for Province, Area, Sector assuming that are all equals';

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
        // Province
        foreach (Region::all() as $region) {
            $tot = count($region->provinces);
            foreach ($region->provinces as $province) {
                $province->num_expected = floor($region->num_expected / $tot);
                $province->save();
            }
        }
        // Area
        foreach (Province::all() as $province) {
            $tot = count($province->areas);
            foreach ($province->areas as $area) {
                $area->num_expected = floor($province->num_expected / $tot);
                $area->save();
            }
        }
        // Sector
        foreach (Area::all() as $area) {
            $tot = count($area->sectors);
            foreach ($area->sectors as $sector) {
                $sector->num_expected = floor($area->num_expected / $tot);
                $sector->save();
            }
        }
        return 0;
    }
}
